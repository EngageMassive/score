<?php

namespace Takt\Score;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait ExtendsWP
{
    public $exists = true;

    protected $base;

    public function __get($property): mixed
    {
        if (!$this->exists) {
            return false;
        }

        if (property_exists($this->base, $property)) {
            return $this->base->{$property};
        }

        if (method_exists($this, 'get' . Str::camel($property))) {
            $method = Str::camel('get_' . $property);

            return $this->{$method}();
        }

        if (!empty($this->getMeta($property))) {
            return $this->getMeta($property);
        }

        return null;
    }

    public function __isset($property): bool
    {
        if (!$this->exists) {
            return false;
        }

        return property_exists($this->base, $property) ||
            method_exists($this, Str::camel('get_' . $property)) ||
            !empty($this->getMeta($property));
    }

    public function __call($method, $arguments): mixed
    {
        if (method_exists($this->base, $method)) {
            return $this->base->{$method}(...$arguments);
        }
    }

    public function getMeta($key, $single = true): mixed
    {
        return get_post_meta($this->ID, $key, $single);
    }

    protected function getPublishedAt(): Carbon
    {
        return Carbon::parse($this->base->post_date);
    }

    protected function getTitle(): string
    {
        return $this->base->post_title;
    }

    protected function getPermalink(): string
    {
        return get_permalink($this->base);
    }

    protected function getFeaturedImage(): array
    {
        return [
            'src' => get_the_post_thumbnail_url($this->base->ID, 'full'),
            'id' => get_post_thumbnail_id($this->base->ID),
        ];
    }
}
