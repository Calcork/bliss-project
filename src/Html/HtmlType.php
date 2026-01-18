<?php

namespace Hizech\Bliss\Html;

enum HtmlType: string
{
    case Text = 'text';
    case Email = 'email';
    case Password = 'password';
    case Number = 'number';
    case Checkbox = 'checkbox';
    case Radio = 'radio';
    case Select = 'select';
    case Textarea = 'textarea';
    case File = 'file';
    case Hidden = 'hidden';
    case Submit = 'submit';
}
