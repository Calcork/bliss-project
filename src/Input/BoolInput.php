<?php

namespace Hizech\Bliss\Input;

class BoolInput
{
    /** @var array<int, bool> */
    public readonly array $choices;
    /** @var array{'0': string|null, '1': string|null} */
    public readonly array $choices_tids;
    function __construct(
        ?string $zero_tid = 'general.0',
        ?string $one_tid = 'general.1',
    )
    {
        $this->choices = [true, false];
        $this->choices_tids = ['0' => $zero_tid, '1' => $one_tid];
    }


    function validate(string $value) : ?bool {
        $pos = ['1', 'true'];
        $neg = ['0', 'false'];

        $v = null;

        if(in_array($value, $pos)) $v = true;
        elseif(in_array($value, $neg)) $v = false;
        else $v = null;

         if ($v === null) {
            return null;
        }

        if (!in_array($v, $this->choices, true)) {
            return null;
        }

        return $v;

    }

}