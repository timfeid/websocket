<?php

namespace TimFeid\WebSocket;

class Input
{
    protected $raw_input;
    protected $readable_input;
    protected $client;

    public function __construct(Client $client, $input)
    {
        $this->raw_input = $input;
        $this->readable_input = $this->unmask($input);
        $this->client = $client;
    }

    public function unmask($text)
    {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }

        return $text;
    }

    public function __toString()
    {
        return $this->readable_input;
    }
}
