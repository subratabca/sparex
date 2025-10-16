<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use App\Models\ProductImage;

class ImageHelper
{
    public static function processAndSaveImage($image, $configKey, $isMultiple = false, $oldImage = null)
    {
        if (!$image || !$configKey) {
            return null;
        }

        $config = config("image.$configKey");
        $paths = $config['paths'];
        $sizes = $config['sizes'];

        $manager = new ImageManager(new Driver());
        if ($oldImage) {
            self::deleteOldImages($oldImage, $configKey);
        }

        if ($isMultiple && is_array($image)) {
            $imageNames = [];
            foreach ($image as $imgFile) {
                $imageNames[] = self::saveImage($imgFile, $paths, $sizes, $manager);
            }
            return $imageNames;
        }

        return self::saveImage($image, $paths, $sizes, $manager);
    }

    private static function saveImage($image, $paths, $sizes, $manager)
    {
        $imageName = time() . uniqid() . '.' . $image->getClientOriginalExtension();
        $img = $manager->read($image);

        foreach ($sizes as $key => $size) {
            if (isset($paths[$key])) {
                $path = $paths[$key];
                File::ensureDirectoryExists($path);

                $img->resize($size['width'], $size['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save($path . $imageName);
            }
        }

        return $imageName;
    }

    public static function saveMultiImages($images, $productId)
    {
        $multiImagePaths = self::processAndSaveImage($images, 'multi_images', true);
        foreach ($multiImagePaths as $imagePath) {
            ProductImage::create([
                'product_id' => $productId,
                'image' => $imagePath,
            ]);
        }
    }

    public static function deleteOldImages($imageName, $configKey)
    {
        if ($imageName) {
            $paths = config("image.$configKey.paths");
            $sizes = config("image.$configKey.sizes");

            foreach ($sizes as $key => $size) {
                $imagePath = $paths[$key] . $imageName;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    }

    public static function deleteMultipleImages($images, $configKey)
    {
        $config = config("image.$configKey");
        $path = $config['paths']['multiple'];

        foreach ($images as $image) {
            $imagePath = $path . $image->image;
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }

            $image->delete();
        }
    }

    public static function deleteImagesFromHTML($htmlContent)
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/', $htmlContent, $matches);

        if (isset($matches[1])) {
            foreach ($matches[1] as $imageUrl) {
                $imagePath = ltrim(parse_url($imageUrl, PHP_URL_PATH), '/');
                $fullImagePath = public_path($imagePath);
                if (File::exists($fullImagePath)) {
                    File::delete($fullImagePath);
                }
            }
        }
    }

    public static function processAndSaveProfileImage($image, $paths, $oldImage = null)
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $manager = new ImageManager(new Driver());

        if ($oldImage) {
            foreach (['large', 'medium', 'small'] as $size) {
                $path = $paths[$size] . $oldImage;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        foreach (['large', 'medium', 'small'] as $size) {
            $resizeDimensions = config('image.resize')[$size];
            $manager->read($image)
                ->resize($resizeDimensions['width'], $resizeDimensions['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($paths[$size] . $imageName);
        }

        return $imageName;
    }

    public static function processAndSaveDocumentImage($image, $type, $configKey = 'document', $oldImageName = null, $docType = 'doc1')
    {
        if (!$image || !$type) {
            return null;
        }

        $pathsConfig = config("image.$configKey.{$type}_document_paths") ?? [];
        $resizeConfig = config("image.$configKey.doc_resize") ?? [];

        if ($oldImageName) {
            foreach ($pathsConfig as $path) {
                $oldImagePath = $path . $oldImageName;
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }
        }

        if ($docType === 'doc1') {
            $imageName = time() . '_doc1.' . $image->getClientOriginalExtension();
        } elseif ($docType === 'doc2') {
            $imageName = time() . '_doc2.' . $image->getClientOriginalExtension();
        } else {
            $imageName = time() . uniqid() . '.' . $image->getClientOriginalExtension();
        }

        $manager = new ImageManager(new Driver());

        foreach ($pathsConfig as $size => $path) {
            File::ensureDirectoryExists($path);

            $resizeDimensions = $resizeConfig[$size] ?? null;
            $manager->read($image->getRealPath())
                ->resize(
                    $resizeDimensions['width'] ?? null,
                    $resizeDimensions['height'] ?? null,
                    function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                )
                ->save($path . $imageName);
        }

        return $imageName;
    }
}
