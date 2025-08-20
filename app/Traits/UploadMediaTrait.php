<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait UploadMediaTrait
{
    public function uploadMedia(Request $request, $fileKey)
    {
        $allowedKeys = ['profile_image', 'cover_image', 'resume', 'video', 'audio','document','job_attachment'];

        if (!in_array($fileKey, $allowedKeys)) {
            return [
                'error' => true,
                'message' => 'Invalid file_key. Allowed keys: ' . implode(', ', $allowedKeys),
                'errors' => null
            ];
        }

        $validator = Validator::make($request->all(), [
            $fileKey => 'required|file|max:5120|mimes:jpeg,png,jpg,gif,mp4,avi,pdf,doc,docx,mp3,wav'
        ]);

        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ];
        }

        $path = $request->file($fileKey)->store($fileKey, 'public');
        $url = asset('storage/' . $path);

        return [
            'error' => false,
            'url' => $url
        ];
    }
}
