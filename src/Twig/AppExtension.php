<?php

namespace App\Twig;

use App\Entity\Image;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('thumbnail', [$this, 'thumbnail']),
        ];
    }

    public function thumbnail(Image $image): string
    {
        return cloudinary_url($image->getFile(), [
            'format' => $image->getFormat(),
            'type' => 'private',
            'width' => 400,
            'height' => 300,
            'crop' => 'fill',
            'gravity' => 'auto',
        ]);
    }
}
