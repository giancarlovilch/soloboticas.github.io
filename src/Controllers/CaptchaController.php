<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Helpers/Captcha.php';

class CaptchaController extends Controller
{
    public function generate(): void
    {
        $data = Captcha::generate();
        $this->success('Captcha generado', $data);
    }
}
