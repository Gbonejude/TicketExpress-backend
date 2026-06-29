<?php

use App\Services\Otp\RandomOtpGenerator;

it('generates a string of exactly 6 digits by default', function () {
    $code = (new RandomOtpGenerator)->generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(6)
        ->toMatch('/^\d{6}$/');
});

it('pads with leading zeros so the output is always the correct length', function () {
    // We call generate many times; at least one should potentially be < 100000
    $generator = new RandomOtpGenerator;
    $codes = array_map(fn () => $generator->generate(), range(1, 50));

    foreach ($codes as $code) {
        expect($code)->toHaveLength(6);
    }
});

it('generates a code with the requested number of digits', function () {
    $code = (new RandomOtpGenerator)->generate(4);

    expect($code)
        ->toHaveLength(4)
        ->toMatch('/^\d{4}$/');
});

it('generates different codes across multiple calls', function () {
    $generator = new RandomOtpGenerator;
    $codes = array_map(fn () => $generator->generate(), range(1, 10));

    expect(count(array_unique($codes)))->toBeGreaterThan(1);
});
