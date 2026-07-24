<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiInsightService
{
    public function generateDashboardInsight(array $data): string
    {
        return $this->generate($this->buildDashboardPrompt($data), 550);
    }

    public function generateReportInsight(array $data): string
    {
        return $this->generate($this->buildReportPrompt($data), 650);
    }

    public function generateBillingDraft(array $data): string
    {
        return $this->generate($this->buildBillingDraftPrompt($data), 850);
    }
    private function generate(string $prompt, int $maxOutputTokens): string
    {
        $apiKey = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-3.5-flash-lite');

        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(30)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [[
                        'text' => $prompt,
                    ]],
                ]],
                'generationConfig' => [
                    'maxOutputTokens' => $maxOutputTokens,
                ],
            ]);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?: 'Gagal menghubungi Gemini API.';
            throw new RuntimeException($message);
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini tidak mengembalikan insight.');
        }

        return trim($text);
    }

    private function buildDashboardPrompt(array $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Peran: analis internal BAPENDA untuk membaca dashboard PBB.
Tugas: tulis catatan singkat yang membantu petugas atau pimpinan memahami kondisi data hari ini.

Aturan penulisan:
- Gunakan Bahasa Indonesia yang wajar, ringkas, dan tidak terdengar seperti chatbot.
- Jangan memakai frasa seperti "berdasarkan data yang diberikan", "sebagai AI", "berikut adalah", atau kalimat pembuka yang terlalu umum.
- Jangan mengarang angka, nama wilayah, atau kesimpulan di luar data.
- Jangan menyebut atau meminta NIK, KTP, alamat lengkap, atau data pribadi mentah.
- Jika data kosong/minim, katakan secara langsung bahwa catatan masih terbatas.
- Maksimal 120 kata.

Data agregat dashboard:
{$json}

Format jawaban wajib:
Catatan Analisis:
[Tulis 2-3 kalimat pendek]

Tindak Lanjut:
- [Saran konkret 1]
- [Saran konkret 2]
- [Saran konkret 3]
PROMPT;
    }

    private function buildReportPrompt(array $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Peran: staf penyusun laporan PBB BAPENDA.
Tugas: buat narasi singkat laporan penerimaan yang siap ditempel ke catatan pimpinan.

Aturan penulisan:
- Bahasa Indonesia formal, sederhana, dan tidak terdengar seperti chatbot.
- Jangan memakai frasa "berdasarkan data yang diberikan", "sebagai AI", atau pembuka panjang.
- Jangan mengarang angka, periode, nama wajib pajak, NOP, atau kesimpulan di luar data.
- Fokus pada penerimaan, periode, jumlah data, dan tindak lanjut administratif.
- Jika data kosong/minim, tulis dengan jujur bahwa laporan belum cukup kuat untuk disimpulkan.
- Maksimal 140 kata.

Data agregat laporan:
{$json}

Format jawaban wajib:
Ringkasan Laporan:
[Tulis 2-3 kalimat pendek]

Catatan Pimpinan:
- [Catatan konkret 1]
- [Catatan konkret 2]
- [Catatan konkret 3]
PROMPT;
    }
    private function buildBillingDraftPrompt(array $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Peran: staf administrasi BAPENDA yang membantu menyusun pengingat tagihan PBB.
Tugas: buat draft surat/pesan tagihan yang rapi, sopan, dan siap diperiksa petugas.

Aturan penulisan:
- Gunakan Bahasa Indonesia formal yang natural, bukan gaya chatbot.
- Jangan memakai frasa "berdasarkan data yang diberikan", "sebagai AI", atau pembuka yang berlebihan.
- Jangan mengarang alamat, NIK/KTP, nomor surat, tanggal jatuh tempo, dasar hukum, denda, atau status tunggakan jika tidak ada di data.
- Jangan menyatakan surat sudah resmi; tulis sebagai draft yang perlu diverifikasi petugas.
- Sertakan nilai PBB, tahun pajak, NOP, dan nama wajib pajak jika tersedia.
- Buat redaksi tegas tetapi tidak mengancam.

Data PBB:
{$json}

Format jawaban wajib:
Draft Surat:
[Tulis draft 4-6 paragraf pendek]

Pesan Singkat:
[Tulis versi WhatsApp/SMS maksimal 3 kalimat]

Catatan Petugas:
- [Hal yang perlu dicek sebelum dikirim]
- [Hal yang tidak boleh dilupakan]
PROMPT;
    }
}

