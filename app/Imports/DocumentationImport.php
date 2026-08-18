<?php

namespace App\Imports;

use App\Models\Documentation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Str;

class DocumentationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsErrors
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip if title is empty
        if (empty($row['title'])) {
            return null;
        }

        // Generate slug
        $slug = Str::slug($row['title']);
        $counter = 1;
        
        while (Documentation::where('slug', $slug)->exists()) {
            $slug = Str::slug($row['title']) . '-' . $counter;
            $counter++;
        }

        // Parse tags
        $tags = [];
        if (!empty($row['tags'])) {
            $tags = array_map('trim', explode(',', $row['tags']));
        }

        return new Documentation([
            'title' => $row['title'],
            'content' => $row['content'] ?? '',
            'category' => $row['category'] ?? 'General',
            'tags' => $tags,
            'meta_description' => $row['meta_description'] ?? null,
            'order' => $row['order'] ?? 0,
            'is_active' => strtolower($row['status'] ?? 'active') === 'active',
            'slug' => $slug
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'meta_description' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive,Active,Inactive'
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'title.required' => 'Title is required for documentation articles.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'meta_description.max' => 'Meta description cannot exceed 255 characters.',
            'order.integer' => 'Order must be a number.',
            'order.min' => 'Order cannot be negative.',
            'status.in' => 'Status must be either "active" or "inactive".'
        ];
    }
} 