<?php

namespace App\Helpers;
use Illuminate\Support\Str;



class Helper
{

    //! File or Image Upload
    public static function fileUpload($file, string $folder, string $name): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        $imageName = Str::slug($name) . '.' . $file->extension();
        $path      = public_path('uploads/' . $folder);
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        $file->move($path, $imageName);
        return 'uploads/' . $folder . '/' . $imageName;
    }

    //! File or Image Delete
    public static function fileDelete(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    //! Generate Slug
    public static function makeSlug($model, string $title): string
    {
        $slug = Str::slug($title);
        while ($model::where('slug', $slug)->exists()) {
            $randomString = Str::random(5);
            $slug         = Str::slug($title) . '-' . $randomString;
        }
        return $slug;
    }
}
