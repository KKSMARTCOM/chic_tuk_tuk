<?php

namespace App\Services;

use App\Models\Testimonial;

class TestimonialService
{
    public function getTestimonials()
    {
        return  Testimonial::with('user')
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();
    }
}
