<?php

namespace App\Console\Commands;

use App\Jobs\LogClickJob;
use App\Models\Click;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

use function Illuminate\Log\log;

class FlushClicksCommand extends Command
{
    protected $signature = 'clicks:flush';

    protected $description = 'Flush buffered click data from Redis into the database';

    public function handle(): int
    {
        $batchSize = (int) config('clicks.batch_size', 100);
        $totalFlushed = 0;

        do {
            $rows = [];

            while (count($rows) < $batchSize) {
                $item = Redis::lpop(LogClickJob::REDIS_KEY);

                if ($item === null || $item === '') {
                    break;
                }

                $decoded = json_decode($item, true);

                if (! is_array($decoded)) {
                    break;
                }

                $rows[] = $decoded;
            }

            if (empty($rows)) {
                break;
            }

            Click::insert($rows);
            $totalFlushed += count($rows);

        } while (Redis::llen(LogClickJob::REDIS_KEY) >= $batchSize);

        $this->info("Flushed {$totalFlushed} click(s) to the database.");

        return self::SUCCESS;
    }
}
