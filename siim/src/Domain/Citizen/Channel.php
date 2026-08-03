<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen;

enum Channel: string
{
    case MetaFacebook = 'meta_facebook';
    case MetaInstagram = 'meta_instagram';
    case WebForm = 'web_form';
    case CsvUpload = 'csv_upload';
    case PublicChatbot = 'public_chatbot';
    case WebSurvey = 'web_survey';
}
