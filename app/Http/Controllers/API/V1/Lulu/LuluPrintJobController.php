<?php

namespace App\Http\Controllers\API\V1\Lulu;

use App\Http\Controllers\Controller;
use App\Services\API\V1\Lulu\LuluService;
use Illuminate\Http\Request;

class LuluPrintJobController extends Controller
{
    public function createPrintJob()
    {
        $printJobData = [
            "contact_email" => "linzie@test.com",
            "external_id" => "demo-time",
            "line_items" => [
                [
                    "external_id" => "item-reference-1",
                    "printable_normalization" => [
                        "cover" => [
                            "source_url" => "https://linzienielsen.softvencefsd.xyz/storage/journal_pdfs/1_cover.pdf"
                        ],
                        "interior" => [
                            "source_url" => "https://linzienielsen.softvencefsd.xyz/storage/journal_pdfs/1.pdf"
                        ],
                        "pod_package_id" => "0600X0900FCPREPB080CW444GXX"
                    ],
                    "quantity" => 1,
                    "title" => "Linzie Journal"
                ]
            ],
            "production_delay" => 120,
            "shipping_address" => [
                "city" => "New York",
                "country_code" => "US",
                "name" => "Jane Doe",
                "phone_number" => "212-555-1234",
                "postcode" => "10001",
                "state_code" => "NY",
                "street1" => "350 5th Ave"
            ],
            "shipping_level" => "MAIL"
        ];

        $response = LuluService::createPrintJob($printJobData);
        return response()->json($response);
    }
}
