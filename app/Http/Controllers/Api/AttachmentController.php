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
            'files.*' => 'required|file|max:51200', // max 50MB
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

        $attachments = [];

        foreach ($request->file('files') as $file) {
            $filename = Str::uuid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('attachments', $filename, 's3');

            $attachment = $parent->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
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
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Request $request, Attachment $attachment)
    {
        if ($attachment->uploaded_by !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete from S3
        if (Storage::disk('s3')->exists($attachment->file_path)) {
            Storage::disk('s3')->delete($attachment->file_path);
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

        // Generate temporary URL valid for 15 minutes
        $url = Storage::disk('s3')->temporaryUrl(
            $attachment->file_path,
            now()->addMinutes(15)
        );

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
