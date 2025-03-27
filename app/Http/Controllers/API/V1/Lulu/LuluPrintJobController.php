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
                            "source_url" => "https://www.dropbox.com/s/7bv6mg2tj0h3l0r/lulu_trade_perfect_template.pdf?dl=1&raw=1"
                        ],
                        "interior" => [
                            "source_url" => "https://www.dropbox.com/s/r20orb8umqjzav9/lulu_trade_interior_template-32.pdf?dl=1&raw=1"
                        ],
                        "pod_package_id" => "0600X0900BWSTDPB060UW444MXX"
                    ],
                    "quantity" => 1,
                    "title" => "Linzie Journal"
                ]
            ],
            "production_delay" => 120,
            "shipping_address" => [
                "city" => "Lübeck",
                "country_code" => "GB",
                "name" => "Hans Dampf",
                "phone_number" => "844-212-0689",
                "postcode" => "PO1 3AX",
                "state_code" => "",
                "street1" => "Holstenstr. 48"
            ],
            "shipping_level" => "MAIL"
        ];

        $response = LuluService::createPrintJob($printJobData);
        return response()->json($response);
    }
}
