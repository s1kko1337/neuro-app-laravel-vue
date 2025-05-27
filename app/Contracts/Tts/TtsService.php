<?php

namespace App\Contracts\Tts;

interface TtsService
{
    /**
     * Синтезировать речь из текста
     *
     * @param string $text
     * @param string $language
     * @return string
     * @throws \Exception
     */
    public function synthesize(string $text, string $language): string;
}
