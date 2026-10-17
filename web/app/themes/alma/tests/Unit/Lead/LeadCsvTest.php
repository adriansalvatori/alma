<?php

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Services\LeadCsv;

it('neutralises values a spreadsheet would run as formulas', function (string $value, string $expected) {
    expect(LeadCsv::cell($value))->toBe($expected);
})->with([
    'equals' => ['=HYPERLINK("http://evil")', '\'=HYPERLINK("http://evil")'],
    'plus' => ['+1+1', "'+1+1"],
    'minus' => ['-2', "'-2"],
    'at' => ['@SUM(A1)', "'@SUM(A1)"],
    'tab' => ["\tx", "'\tx"],
    'plain' => ['Ada Lovelace', 'Ada Lovelace'],
    'empty' => ['', ''],
]);

it('maps a lead to one escaped row in column order', function () {
    $lead = new Lead([
        'name' => '=cmd',
        'email' => 'ada@example.com',
        'form_key' => 'contact',
        'consent' => true,
        'fields' => ['budget' => '10k'],
        'attribution' => ['first' => ['source' => 'google', 'medium' => 'cpc'], 'last' => ['source' => 'newsletter']],
    ]);

    $row = array_combine(array_keys(LeadCsv::COLUMNS), (new LeadCsv)->row($lead));

    expect($row)->toMatchArray([
        'name' => "'=cmd",
        'email' => 'ada@example.com',
        'status' => 'new',
        'consent' => 'yes',
        'first_source' => 'google',
        'first_medium' => 'cpc',
        'last_source' => 'newsletter',
        'fields' => '{"budget":"10k"}',
    ]);
});
