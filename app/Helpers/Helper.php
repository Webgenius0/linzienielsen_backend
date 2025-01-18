<?php
namespace App\Helpers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class Helper
{
    /**
     * Upload an image and return its URL.
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @param  string  $directory
     * @return string
     */
    public static function uploadFile($image, $directory)
    {
        try {
            $imageFileName = uniqid('image_') . '.' . $image->getClientOriginalExtension();
            $image->storeAs($directory, $imageFileName, 'public');
            return $directory . '/' . $imageFileName;
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong');
        }
    }


    /**
     * Delete an image and return a boolean.
     *
     * @param  string  $imageUrl
     * @return bool
     */
    public static function deleteFile($imageUrl)
    {
        try {
            // Check if $imageUrl is a valid string
            if (is_string($imageUrl) && !empty($imageUrl)) {
                // Extract the relative path from the URL
                $parsedUrl = parse_url($imageUrl);
                $relativePath = $parsedUrl['path'] ?? '';

                // Remove the leading '/storage/' from the path
                $relativePath = preg_replace('/^\/?storage\//', '', $relativePath);

                // Check if the image exists
                if (Storage::disk('public')->exists($relativePath)) {
                    // Delete the image if it exists
                    Storage::disk('public')->delete($relativePath);
                    return true;
                } else {
                    // Return false if the image does not exist
                    return false;
                }
            } else {
                // Return false if $imageUrl is not a valid string
                return false;
            }
        } catch (Exception $e) {
            // Handle any other exceptions
            return false;
        }
    }



    /**
     * Generate a unique slug for the given model and title.
     *
     * @param string $title
     * @param string $table
     * @param string $slugColumn
     * @return string
     */
    public static function generateUniqueSlug($title, $table, $slugColumn = 'slug')
    {
        // Generate initial slug
        $slug = str::slug($title);

        // Check if the slug exists
        $count = DB::table($table)->where($slugColumn, 'LIKE', "$slug%")->count();

        // If it exists, append the count
        return $count ? "{$slug}-{$count}" : $slug;
    }


    /**
     * Generate a unique 10-character SKU for a user based on timestamp and random string,
     * ensuring it does not already exist in the specified table.
     *
     * @param int $userId The user ID for whom the SKU is generated.
     * @param string $tableName The name of the table in which to check for SKU uniqueness.
     * @return string The generated SKU.
     */
    public static function generateUniqueId($table, $column, $length = 10)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);

        do {
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            // Check if SKU is already present in the table
            $exists = DB::table($table)->where($column, $randomString)->exists();
        } while ($exists);

        return $randomString;
    }



    /**
     * Returns a standardized success response with the provided data, message, and HTTP status code.
     *
     * This method formats the response to indicate a successful operation. It includes a success
     * flag, an optional message, the data to be returned, and an HTTP status code. The response
     * is structured as a JSON object and the status code defaults to 200 (OK), but can be customized.
     *
     * @param mixed $data The data to be included in the response, typically the result of the operation.
     * @param string|null $message An optional message providing additional context about the success.
     * @param int $code The HTTP status code for the response (default is 200).
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success status, message, data, and code.
     */
    public static function success($code = 200, $message = null, $data = []): JsonResponse
    {
        return response()->json([
            'success' => (bool) true,
            'code' => (int) $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String() . ' GMT' . now()->format('P'),
        ], $code);
    }




    /**
     * Returns a standardized error response with the provided data, message, and HTTP status code.
     *
     * This method formats the response to indicate an error or failure in the operation. It includes
     * an error flag, an optional message, the error details or data, and an HTTP status code. The
     * response is structured as a JSON object, and the status code defaults to 500 (Internal Server Error),
     * but can be customized to reflect different types of errors.
     *
     * @param mixed $data The data to be included in the response, typically containing error details.
     * @param string|null $message An optional message providing additional context about the error.
     * @param int $code The HTTP status code for the response (default is 500).
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the error status, message, data, and code.
     */
    public static function error($code = 500, $message = null, $error = []): JsonResponse
    {
        return response()->json([
            'status' => (bool) false,
            'code' => (int) $code,
            'message' => $message,
            'error' => $error,
            'timestamp' => now()->toIso8601String() . ' GMT' . now()->format('P'),
        ], $code);
    }
}
