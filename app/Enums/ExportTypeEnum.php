<?php

namespace App\Enums;

enum ExportTypeEnum: int
{
    case ONLY_EXPORT = 0;
    case EXPORT_AND_DELETE = 1;
}
