<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Click Batch Size
    |--------------------------------------------------------------------------
    |
    | The number of click records to buffer in Redis before triggering an
    | immediate flush to the database. Regardless of this threshold, the
    | scheduler will flush all buffered clicks every minute.
    |
    */

    'batch_size' => (int) env('CLICK_BATCH_SIZE', 100),

];
