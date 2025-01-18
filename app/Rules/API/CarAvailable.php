<?php

namespace App\Rules\API;

use App\Models\CarTestDrive;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class CarAvailable implements ValidationRule
{
    protected $start;
    protected $end;

    protected $car;


    /**
     * Constructor to accept start and end timestamps for validation.
     *
     * @param string $start
     * @param string $end
     */
    public function __construct($date, $time, $car)
    {
        // Parse the start time
        $startCarbon = Carbon::parse("$date $time", 'UTC');

        // Clone and add hours for the end time
        $endCarbon = $startCarbon->copy()->addHours(2);


        $this->start = $startCarbon->toIso8601String();
        $this->end = $endCarbon->toIso8601String();
        $this->car = $car;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $startTimestamp = $this->start;
        $endTimestamp = $this->end;
        $existingBooking = CarTestDrive::where('car_id', $this->car)
            ->where(function ($query) use ($startTimestamp, $endTimestamp) {
                $query->whereBetween('start', [$startTimestamp, $endTimestamp])
                    ->orWhereBetween('end', [$startTimestamp, $endTimestamp])
                    ->orWhere(function ($query) use ($startTimestamp, $endTimestamp) {
                        $query->where('start', '<', $startTimestamp)
                            ->where('end', '>', $endTimestamp);
                    });
            })
            ->exists();
        if ($existingBooking) {
            $fail('The car is already booked during this time range.');
        }
    }
}
