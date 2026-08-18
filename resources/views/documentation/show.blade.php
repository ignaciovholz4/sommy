@extends('layouts.admin')

@section('title', $doc->title)

@section('styles')
<style>
.documentation-content {
    line-height: 1.8;
    font-size: 1.1rem;
}

.documentation-content h1,
.documentation-content h2,
.documentation-content h3,
.documentation-content h4,
.documentation-content h5,
.documentation-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.documentation-content h1 {
    font-size: 2.5rem;
    border-bottom: 3px solid #007bff;
    padding-bottom: 0.5rem;
}

.documentation-content h2 {
    font-size: 2rem;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.3rem;
}

.documentation-content h3 {
    font-size: 1.5rem;
    color: #495057;
}

.documentation-content p {
    margin-bottom: 1.5rem;
}

.documentation-content ul,
.documentation-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.documentation-content li {
    margin-bottom: 0.5rem;
}

.documentation-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #6c757d;
}

.documentation-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

.documentation-content pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.related-articles {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
}

.article-meta {
    background: #e9ecef;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 2rem;
}
</style>
@endsection

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ $doc->title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('documentation.index') }}">Documentation</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('documentation.category', $doc->category) }}">{{ $doc->category }}</a></li>
                    <li class="breadcrumb-item active">{{ $doc->title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Article Meta -->
                        <div class="article-meta">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Category:</strong> 
                                    <span class="badge badge-info">{{ $doc->category }}</span>
                                </div>
                                <div class="col-md-6 text-right">
                                    <strong>Last Updated:</strong> 
                                    {{ $doc->updated_at->format('M d, Y') }}
                                </div>
                            </div>
                            @if($doc->tags && count($doc->tags) > 0)
                                <div class="mt-2">
                                    <strong>Tags:</strong>
                                    @foreach($doc->tags as $tag)
                                        <span class="badge badge-secondary">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Article Content -->
                        <div class="documentation-content">
                            {!! nl2br(e($doc->content)) !!}
                        </div>

                        <!-- Article Footer -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('documentation.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Documentation
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('chatbot.index') }}" class="btn btn-primary">
                                        <i class="fas fa-robot"></i> Ask AI Assistant
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Related Articles -->
                @if($relatedDocs->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-link"></i> Related Articles
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="related-articles">
                                @foreach($relatedDocs as $relatedDoc)
                                    <div class="mb-3 pb-3 border-bottom">
                                        <h6>
                                            <a href="{{ route('documentation.show', $relatedDoc->slug) }}" class="text-decoration-none">
                                                {{ $relatedDoc->title }}
                                            </a>
                                        </h6>
                                        <p class="text-muted small mb-1">
                                            {{ Str::limit($relatedDoc->meta_description ?: $relatedDoc->content, 80) }}
                                        </p>
                                        <span class="badge badge-sm badge-outline-secondary">{{ $relatedDoc->category }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Category Navigation -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-folder"></i> Browse by Category
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @php
                                $categories = \App\Models\Documentation::active()->distinct()->pluck('category');
                            @endphp
                            @foreach($categories as $category)
                                <a href="{{ route('documentation.category', $category) }}" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $category == $doc->category ? 'active' : '' }}">
                                    {{ $category }}
                                    <span class="badge badge-primary badge-pill">
                                        {{ \App\Models\Documentation::active()->where('category', $category)->count() }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tools"></i> Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('chatbot.index') }}" class="btn btn-primary">
                                <i class="fas fa-robot"></i> Ask AI Assistant
                            </a>
                            <a href="{{ route('documentation.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i> Search Documentation
                            </a>
                            <a href="{{ route('documentation.category', $doc->category) }}" class="btn btn-outline-info">
                                <i class="fas fa-folder-open"></i> More {{ $doc->category }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
