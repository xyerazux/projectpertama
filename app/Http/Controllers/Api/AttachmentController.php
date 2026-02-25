<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    /**
     * Store a newly created attachment in storage.
     */
    public function store(Request $request, $type, $id)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'required|file|max:50000', // Universal mime max:50000
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($type === 'tasks' || $type === 'task') {
            $parent = Task::findOrFail($id);
            // Ensure auth logic if task belongs to user (assuming handled by policies or directly)
            if ($parent->user_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } elseif ($type === 'roadmaps' || $type === 'roadmap') {
            $parent = Roadmap::findOrFail($id);
            if ($parent->user_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => "Invalid generic type: {$type}."], 400);
        }

        try {
            $attachments = [];

            // Pastikan folder exist dengan opsi rekursif dan CHMOD 0775
            $storagePath = storage_path('app/public/attachments');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0775, true);
            }

            foreach ($request->file('files') as $file) {
                $extension = $file->getClientOriginalExtension() ?: 'bin';
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $shortName = substr($originalName, 0, 100) . '.' . $extension;
                $filename = time() . '_' . Str::random(10) . '.' . $extension;
                $path = $file->storeAs('attachments', $filename, 'public');

                $attachment = $parent->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $shortName,
                    'file_mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $request->user()->id,
                ]);

                $attachments[] = $attachment;
            }

            return response()->json([
                'success' => true,
                'message' => 'Attachments uploaded successfully',
                'data' => $attachments,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Request $request, Attachment $attachment)
    {
        if ($attachment->uploaded_by !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete from local public storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully',
        ]);
    }

    /**
     * Get a temporary signed URL for viewing the attachment.
     */
    public function view(Request $request, Attachment $attachment)
    {
        // Ideally we check if user has access to the parent task/roadmap. 
        // For simplicity, checking if uploaded by them or part of their items.
        $attachable = $attachment->attachable;
        if ($attachable && $attachable->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Generate public URL
        $url = rtrim(config('app.url'), '/') . '/storage/attachments/' . basename($attachment->file_path);

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'file_name' => $attachment->file_name,
                'file_mime_type' => $attachment->file_mime_type,
            ]
        ]);
    }
}
