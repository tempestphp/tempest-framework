<?php

declare(strict_types=1);

namespace Tempest\Http;

enum ContentType: string
{
    public const string HEADER = 'Content-Type';

    case AAC = 'audio/aac';
    case ABW = 'application/x-abiword';
    case APNG = 'image/apng';
    case ARC = 'application/x-freearc';
    case AVIF = 'image/avif';
    case AVI = 'video/x-msvideo';
    case AZW = 'application/vnd.amazon.ebook';
    case BIN = 'application/octet-stream';
    case BMP = 'image/bmp';
    case BZ = 'application/x-bzip';
    case BZ2 = 'application/x-bzip2';
    case CDA = 'application/x-cdf';
    case CSH = 'application/x-csh';
    case CSS = 'text/css';
    case CSV = 'text/csv';
    case DOC = 'application/msword';
    case DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    case EOT = 'application/vnd.ms-fontobject';
    case EPUB = 'application/epub+zip';
    case EVENT_STREAM = 'text/event-stream';
    case GZ = 'application/gzip';
    case GIF = 'image/gif';
    case HTML = 'text/html';
    case ICO = 'image/vnd.microsoft.icon';
    case ICS = 'text/calendar';
    case JAR = 'application/java-archive';
    case JPEG = 'image/jpeg';
    case JS = 'text/javascript';
    case JSON = 'application/json';
    case JSONLD = 'application/ld+json';
    case MID = 'audio/midi,';
    case MP3 = 'audio/mpeg';
    case MP4 = 'video/mp4';
    case MPEG = 'video/mpeg';
    case MPKG = 'application/vnd.apple.installer+xml';
    case ODP = 'application/vnd.oasis.opendocument.presentation';
    case ODS = 'application/vnd.oasis.opendocument.spreadsheet';
    case ODT = 'application/vnd.oasis.opendocument.text';
    case OGA = 'audio/ogg';
    case OGV = 'video/ogg';
    case OGX = 'application/ogg';
    case OPUS = 'audio/opus';
    case OTF = 'font/otf';
    case PNG = 'image/png';
    case PDF = 'application/pdf';
    case PHP = 'application/x-httpd-php';
    case PPT = 'application/vnd.ms-powerpoint';
    case PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    case RAR = 'application/vnd.rar';
    case RTF = 'application/rtf';
    case SH = 'application/x-sh';
    case SVG = 'image/svg+xml';
    case TAR = 'application/x-tar';
    case TIF = 'image/tiff';
    case TS = 'video/mp2t';
    case TTF = 'font/ttf';
    case TXT = 'text/plain';
    case VSD = 'application/vnd.visio';
    case WAV = 'audio/wav';
    case WEBA = 'audio/webm';
    case WEBM = 'video/webm';
    case WEBP = 'image/webp';
    case WOFF = 'font/woff';
    case WOFF2 = 'font/woff2';
    case XHTML = 'application/xhtml+xml';
    case XLS = 'application/vnd.ms-excel';
    case XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    case XML = 'application/xml';
    case XUL = 'application/vnd.mozilla.xul+xml';
    case ZIP = 'application/zip';

    public static function fromPath(string $path): self
    {
        return self::from(mime_content_type($path));
    }
}
