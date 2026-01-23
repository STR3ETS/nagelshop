<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    public function next(string $key = 'INV', int $pad = 6): string
    {
        return DB::transaction(function () use ($key, $pad) {
            $row = DB::table('invoice_sequences')
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('invoice_sequences')->insert([
                    'key' => $key,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = (object) ['last_number' => 0];
            }

            $next = ((int)$row->last_number) + 1;

            DB::table('invoice_sequences')
                ->where('key', $key)
                ->update([
                    'last_number' => $next,
                    'updated_at' => now(),
                ]);

            return $key . '-' . str_pad((string)$next, $pad, '0', STR_PAD_LEFT);
        }, 5);
    }
}
