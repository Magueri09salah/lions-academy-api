<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationConcours\IndexRegistrationConcoursRequest;
use App\Models\RegistrationConcours;
use App\Services\Export\SimpleXlsxBuilder;
use App\Services\RegistrationConcours\RegistrationConcoursQuery;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV + XLSX export of ENA leads. Mirrors the same filter pipeline as
 * the JSON list — "what you see is what you export".
 *
 * Columns chosen for the marketing team's segmentation needs:
 * ville / filière / note régionale / format souhaité / priorité.
 */
class RegistrationConcoursExportController extends Controller
{
    public function __construct(private readonly RegistrationConcoursQuery $query) {}

    public function csv(IndexRegistrationConcoursRequest $request): StreamedResponse
    {
        $request->user()?->can('export', RegistrationConcours::class) ?: abort(403);

        $filename = sprintf('lions-academy-concours-ena-%s.csv', now()->format('Y-m-d_His'));

        $generator = $this->query->forExport($request);

        return response()->streamDownload(function () use ($generator): void {
            $out = fopen('php://output', 'wb');
            if ($out === false) return;

            // UTF-8 BOM so Excel renders accents correctly.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID',
                'Date de soumission',
                'Nom complet',
                'Email',
                'WhatsApp',
                'Ville',
                'Filière',
                'Note régionale',
                'Format souhaité',
                'A déjà passé le concours ENA',
                'Statut',
                'Priorité',
            ], ';');

            foreach ($generator as $r) {
                /** @var RegistrationConcours $r */
                fputcsv($out, [
                    $r->id,
                    $r->submitted_at?->toDateTimeString(),
                    $r->full_name,
                    $r->email,
                    $r->whatsapp_phone,
                    $r->city,
                    $r->filiere?->label(),
                    $r->regional_grade?->label(),
                    $r->preferred_format?->label(),
                    $r->passed_ena_before ? 'Oui' : 'Non',
                    $r->status?->label(),
                    $r->priority?->label(),
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function xlsx(IndexRegistrationConcoursRequest $request): BinaryFileResponse
    {
        $request->user()?->can('export', RegistrationConcours::class) ?: abort(403);

        $filename = sprintf('lions-academy-concours-ena-%s.xlsx', now()->format('Y-m-d_His'));

        $builder = (new SimpleXlsxBuilder())
            ->sheetName('Concours ENA')
            ->headers([
                'ID',
                'Date de soumission',
                'Nom complet',
                'Email',
                'WhatsApp',
                'Ville',
                'Filière',
                'Note régionale',
                'Format souhaité',
                'A déjà passé le concours ENA',
                'Statut',
                'Priorité',
            ])
            ->rows($this->xlsxRows($request));

        $tempPath = $builder->writeToTempFile();

        return response()
            ->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ])
            ->deleteFileAfterSend(true);
    }

    /** @return \Generator<int, array<int, scalar|null>> */
    private function xlsxRows(IndexRegistrationConcoursRequest $request): \Generator
    {
        foreach ($this->query->forExport($request) as $r) {
            /** @var RegistrationConcours $r */
            yield [
                $r->id,
                $r->submitted_at?->toDateTimeString(),
                $r->full_name,
                $r->email,
                $r->whatsapp_phone,
                $r->city,
                $r->filiere?->label(),
                $r->regional_grade?->label(),
                $r->preferred_format?->label(),
                $r->passed_ena_before ? 'Oui' : 'Non',
                $r->status?->label(),
                $r->priority?->label(),
            ];
        }
    }
}
