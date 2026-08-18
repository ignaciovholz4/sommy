@extends('layouts.admin')

@section('title', $category . ' Documentation')

@section('styles')
<style>
.documentation-card {
    transition: transform 0.2s;
    height: 100%;
}

.documentation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.category-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.category-stats {
    background: rgba(255,255,255,0.1);
    padding: 1rem;
    border-radius: 5px;
    margin-top: 1rem;
}
</style>
@endsection

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ $category }} Documentation</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('documentation.index') }}">Documentation</a></li>
                    <li class="breadcrumb-item active">{{ $category }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Category Header -->
        <div class="category-header">
            <div class="row">
                <div class="col-md-8">
                    <h2><i class="fas fa-folder-open"></i> {{ $category }}</h2>
                    <p class="mb-0">Browse all documentation articles in the {{ $category }} category.</p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="category-stats">
                        <h4>{{ $docs->count() }}</h4>
                        <p class="mb-0">Articles</p>
                    </div>
                </div>
            </div>
        </div>

        @if($docs->count() > 0)
            <!-- Articles Grid -->
            <div class="row">
                @foreach($docs as $doc)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card documentation-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="{{ route('documentation.show', $doc->slug) }}" class="text-decoration-none">
                                        {{ $doc->title }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted">
                                    {{ Str::limit($doc->meta_description ?: $doc->content, 120) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> {{ $doc->created_at->format('M d, Y') }}
                                    </small>
                                    @if($doc->tags && count($doc->tags) > 0)
                                        <div>
                                            @foreach(array_slice($doc->tags, 0, 2) as $tag)
                                                <span class="badge badge-sm badge-secondary">{{ $tag }}</span>
                                            @endforeach
                                            @if(count($doc->tags) > 2)
                                                <span class="badge badge-sm badge-secondary">+{{ count($doc->tags) - 2 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('documentation.show', $doc->slug) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Read More
                                    </a>
                                    <a href="{{ route('chatbot.index') }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-robot"></i> Ask AI
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Category Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>Need help with {{ $category }}?</h5>
                            <p class="text-muted">Can't find what you're looking for? Our AI assistant can help!</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('chatbot.index') }}" class="btn btn-primary">
                                    <i class="fas fa-robot"></i> Ask AI Assistant
                                </a>
                                <a href="{{ route('documentation.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-search"></i> Search All Documentation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Articles Found -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5>No articles found in this category</h5>
                            <p class="text-muted">There are no documentation articles in the "{{ $category }}" category yet.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('documentation.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Browse All Documentation
                                </a>
                                <a href="{{ route('chatbot.index') }}" class="btn btn-outline-info">
                                    <i class="fas fa-robot"></i> Ask AI Assistant
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Related Categories -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-th-large"></i> Other Categories
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $otherCategories = \App\Models\Documentation::active()
                                    ->where('category', '!=', $category)
                                    ->distinct()
                                    ->pluck('category')
                                    ->take(6);
                            @endphp
                            @foreach($otherCategories as $otherCategory)
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <a href="{{ route('documentation.category', $otherCategory) }}" 
                                       class="btn btn-outline-primary btn-block text-left">
                                        <i class="fas fa-folder"></i> {{ $otherCategory }}
                                        <span class="badge badge-primary float-right">
                                            {{ \App\Models\Documentation::active()->where('category', $otherCategory)->count() }}
                                        </span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
