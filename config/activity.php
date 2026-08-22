<?php

return [
    'retention_days' => max(30, (int) env('ACTIVITY_RETENTION_DAYS', 180)),
];
