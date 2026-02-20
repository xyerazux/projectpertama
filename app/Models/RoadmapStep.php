<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapStep extends Model
{
    protected $fillable = [
        'roadmap_id', 
        'title', 
        'is_completed',
        'priority',   
        'due_date',   
        'description',
        'category',   
        'progress'    
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'date',
        'progress' => 'integer',
    ];

    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }

    // Helper untuk warna priority
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'high' => 'text-red-600 bg-red-50 border-red-200',
            'medium' => 'text-amber-600 bg-amber-50 border-amber-200',
            'low' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
            default => 'text-slate-600 bg-slate-50 border-slate-200',
        };
    }

    // Helper untuk icon category
    public function getCategoryIconAttribute(): string
{
    $category = strtolower(trim($this->category ?? ''));
    
    return match($category) {
        // 🎨 Design & Creative
        'design', 'ui', 'ux', 'ui/ux', 'interface', 'mockup', 'wireframe' => '🎨',
        'graphics', 'graphic', 'illustration', 'logo', 'branding' => '🖼️',
        'animation', 'motion', 'video' => '🎬',
        'content', 'copy', 'writing', 'blog', 'article' => '✍️',
        
        // 💻 Development & Tech
        'development', 'dev', 'coding', 'code', 'programming' => '💻',
        'frontend', 'front-end', 'html', 'css', 'javascript', 'react', 'vue', 'angular' => '🌐',
        'backend', 'back-end', 'api', 'server', 'database', 'sql', 'mongodb' => '🗄️',
        'mobile', 'android', 'ios', 'app', 'flutter', 'react native' => '📱',
        'deployment', 'deploy', 'production', 'release', 'launch' => '🚀',
        'devops', 'ci/cd', 'docker', 'kubernetes', 'serverless' => '⚙️',
        
        // 🧪 Testing & QA
        'testing', 'qa', 'quality', 'test', 'unit test', 'integration' => '🧪',
        'bug', 'bugfix', 'fix', 'patch', 'error', 'issue' => '🐛',
        'debug', 'debugging', 'troubleshoot' => '🔍',
        
        // 📊 Business & Strategy
        'marketing', 'ads', 'campaign', 'seo', 'sem', 'social media' => '📢',
        'research', 'analysis', 'data', 'survey', 'study' => '📊',
        'planning', 'strategy', 'roadmap', 'milestone' => '📋',
        'meeting', 'discussion', 'sync', 'standup', 'call' => '👥',
        'review', 'feedback', 'approval', 'sign-off' => '✅',
        'analytics', 'metrics', 'kpi', 'reporting', 'dashboard' => '📈',
        
        // 🔐 Security & Performance
        'security', 'auth', 'permission', 'encryption', 'vulnerability' => '🔐',
        'performance', 'optimization', 'speed', 'cache', 'lazy load' => '⚡',
        'monitoring', 'logging', 'alert', 'uptime' => '🔔',
        
        // 📝 Documentation & Support
        'documentation', 'docs', 'wiki', 'guide', 'manual', 'readme' => '📚',
        'support', 'help', 'ticket', 'customer', 'faq' => '🎧',
        'training', 'onboarding', 'tutorial', 'workshop' => '🎓',
        
        // 🔧 Maintenance & Operations
        'maintenance', 'refactor', 'cleanup', 'technical debt' => '🔧',
        'update', 'upgrade', 'migration', 'version' => '🔄',
        'backup', 'restore', 'recovery', 'disaster' => '💾',
        
        // 💡 Ideas & Innovation
        'idea', 'brainstorm', 'innovation', 'experiment', 'prototype' => '💡',
        'feature', 'enhancement', 'improvement', 'new' => '✨',
        'request', 'rfc', 'proposal', 'suggestion' => '🗳️',
        
        // 🌍 Infrastructure & External
        'infrastructure', 'cloud', 'aws', 'gcp', 'azure', 'hosting' => '☁️',
        'integration', 'third-party', 'webhook', 'plugin', 'extension' => '🔌',
        'localization', 'translation', 'i18n', 'l10n', 'language' => '🌐',
        
        // 🎯 Default & Fallbacks
        'task', 'todo', 'general', 'misc', 'other', '' => '📌',
        
        // Fallback untuk kategori custom yang tidak terdaftar
        default => '📌',
    };
}

public function getCategoryColorAttribute(): string
{
    $category = strtolower(trim($this->category ?? ''));
    
    return match($category) {
        // Design & Creative
        'design', 'ui', 'ux', 'graphics', 'animation', 'content' => 'text-pink-600 bg-pink-50 border-pink-200',
        
        // Development
        'development', 'frontend', 'backend', 'mobile', 'deployment', 'devops' => 'text-indigo-600 bg-indigo-50 border-indigo-200',
        
        // Testing & QA
        'testing', 'bug', 'debug', 'qa' => 'text-amber-600 bg-amber-50 border-amber-200',
        
        // Business & Strategy
        'marketing', 'research', 'planning', 'meeting', 'review', 'analytics' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
        
        // Security & Performance
        'security', 'performance', 'monitoring' => 'text-red-600 bg-red-50 border-red-200',
        
        // Documentation & Support
        'documentation', 'support', 'training' => 'text-sky-600 bg-sky-50 border-sky-200',
        
        // Maintenance
        'maintenance', 'update', 'backup' => 'text-slate-600 bg-slate-50 border-slate-200',
        
        // Ideas
        'idea', 'feature', 'request' => 'text-violet-600 bg-violet-50 border-violet-200',
        
        // Infrastructure
        'infrastructure', 'integration', 'localization' => 'text-cyan-600 bg-cyan-50 border-cyan-200',
        
        // Default
        default => 'text-slate-600 bg-slate-50 border-slate-200',
    };
}
}
