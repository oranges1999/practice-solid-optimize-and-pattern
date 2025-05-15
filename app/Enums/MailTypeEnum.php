<?php

namespace App\Enums;

enum MailTypeEnum: int
{
    case ImportMail = 1;
    case ExportMail = 2;
}
