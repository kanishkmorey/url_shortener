<?php

namespace App\Services;

use App\Models\Url;
use App\Support\Base62Encoder;

class UrlShortnerService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private SnowflakeService $snowflake)
    {
        //
    }

    public function generateCode(): string
    {
        try {
            $id = $this->snowflake->nextId();

            if ($id <= 0) {
                throw new \RuntimeException('Snowflake generated a non-positive ID.');
            }

            $code = Base62Encoder::encode($id);

            if ($code === '' || ! preg_match('/^[0-9a-zA-Z]+$/', $code)) {
                throw new \RuntimeException('Generated short code is invalid.');
            }

            return $code;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to generate short code.', 0, $e);
        }
    }

    public function resolveUrl(string $code)
    {
        try {
            $record = Url::where('short_code', $code)->first();

            return $record->url;
        } catch (\Throwable $e) {
            throw new \RuntimeException('No url can be found for the short code.', 0, $e);
        }
    }
}
