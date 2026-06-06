<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\IndexRegistrationRequest;
use App\Models\Registration;
use App\Services\Export\SimpleXlsxBuilder;
use App\Services\Registration\RegistrationQuery;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV export of registrations.
 *
 * Uses the same filter pipeline as the JSON list so the export reflects
 * exactly what the admin sees in the table. Streams the CSV row-by-row
 * (no buffering) so an export over thousands of rows stays under
 * memory pressure.
 *
 * XLSX export is intentionally left out for the launch — the CPS lists
 * "Excel si possible" as nice-to-have. We can plug `maatwebsite/excel`
 * later behind /admin/registrations/export.xlsx without touching the
 * filter or query code.
 */
class RegistrationExportController extends Controller
{
    public function __construct(private readonly RegistrationQuery $query) {}

    public function csv(IndexRegistrationRequest $request): StreamedResponse
    {
        $request->user()?->can('export', Registration::class) ?: abort(403);

        $filename = sprintf('lions-academy-inscriptions-%s.csv', now()->format('Y-m-d_His'));

        $generator = $this->query->forExport($request);

        return response()->streamDownload(function () use ($generator): void {
            $out = fopen('php://output', 'wb');
            if ($out === false) {
                return;
            }

            // Excel-friendly UTF-8 BOM so accents render correctly when the
            // CSV is opened directly in Excel/Numbers.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID',
                'Date de soumission',
                'Nom complet',
                'Email',
                'WhatsApp',
                'Ville',
                'Adresse',
                'Niveau',
                'Profession',
                'Formation',
                'Statut',
                'Message',
                'Documents',
                'IP',
            ], ';');

            foreach ($generator as $r) {
                /** @var Registration $r */
                fputcsv($out, [
                    $r->id,
                    $r->submitted_at?->toDateTimeString(),
                    $r->full_name,
                    $r->email,
                    $r->whatsapp_phone,
                    $r->city,
                    $r->address,
                    $r->education_level,
                    $r->profession,
                    $r->formation_title,
                    $r->status?->label(),
                    $this->flattenMessage((string) $r->message),
                    (int) ($r->documents_count ?? 0),
                    $r->ip_address,
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * GET /api/v1/admin/registrations/export.xlsx
     *
     * Real .xlsx (OOXML), generated on the fly via SimpleXlsxBuilder
     * (PHP ZipArchive). No external dependencies, no temp-disk leftovers.
     * Excel/LibreOffice/Numbers/Google Sheets all open it natively.
     */
    public function xlsx(IndexRegistrationRequest $request): BinaryFileResponse
    {
        $request->user()?->can('export', Registration::class) ?: abort(403);

        $filename = sprintf('lions-academy-inscriptions-%s.xlsx', now()->format('Y-m-d_His'));

        $builder = (new SimpleXlsxBuilder())
            ->sheetName('Inscriptions')
            ->headers([
                'ID',
                'Date de soumission',
                'Nom complet',
                'Email',
                'WhatsApp',
                'Ville',
                'Adresse',
                'Niveau',
                'Profession',
                'Formation',
                'Statut',
                'Message',
                'Documents',
            ])
            ->rows($this->xlsxRows($request));

        $tempPath = $builder->writeToTempFile();

        return response()
            ->download(
                $tempPath,
                $filename,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                ],
            )
            ->deleteFileAfterSend(true);
    }

    /**
     * @return \Generator<int, array<int, scalar|null>>
     */
    private function xlsxRows(IndexRegistrationRequest $request): \Generator
    {
        foreach ($this->query->forExport($request) as $r) {
            /** @var Registration $r */
            yield [
                $r->id,
                $r->submitted_at?->toDateTimeString(),
                $r->full_name,
                $r->email,
                $r->whatsapp_phone,
                $r->city,
                $r->address,
                $r->education_level,
                $r->profession,
                $r->formation_title,
                $r->status?->label(),
                $this->flattenMessage((string) $r->message),
                (int) ($r->documents_count ?? 0),
            ];
        }
    }

    private function flattenMessage(string $message): string
    {
        return trim(preg_replace('/\s+/u', ' ', $message) ?? '');
    }
}
