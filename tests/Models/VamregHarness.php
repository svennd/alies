<?php

declare(strict_types=1);

require_once __DIR__ . '/../Http/Vamreg/VamregHelper.php';

/**
 * Vamregclient whose request() does not touch the network: it records every call and returns the
 * next prepared answer (the last one is repeated). Answers have the shape of the real client:
 * ['http_code' => int, 'raw' => string, 'response' => decoded json] or ['error' => text].
 */
final class VamregModelsRecordingClient extends Vamregclient
{
    /** @var list<array{method: string, path: string, payload: ?string}> */
    public array $requests = [];

    /** @var list<array|callable> */
    private array $answers = [];

    public function __construct(array $answers = [])
    {
        parent::__construct(['apiKey' => 'test-key']);
        $this->answers = $answers ?: [self::answer(200, [])];
    }

    public static function answer(int $code, mixed $response): array
    {
        return ['http_code' => $code, 'raw' => json_encode($response), 'response' => $response];
    }

    public function answers(array $answers): void
    {
        $this->answers = $answers;
    }

    protected function request($method, $path, $payload = null)
    {
        $this->requests[] = ['method' => $method, 'path' => $path, 'payload' => $payload];
        $answer = count($this->answers) > 1 ? array_shift($this->answers) : $this->answers[0];

        return is_callable($answer) ? $answer($method, $path, $payload) : $answer;
    }

    /** The decoded JSON body of request number $i. */
    public function body(int $i = 0): mixed
    {
        return json_decode((string) $this->requests[$i]['payload'], true);
    }
}

/**
 * Runs Vamreg controller methods inside the test process without logging in: the controller is
 * created without its constructor (which needs a session), gets the framework objects of the running
 * CodeIgniter instance, the models and helpers the constructor would load, and a conf array.
 * The client is replaced by a recording client, so nothing is ever sent.
 */
final class VamregHarness
{
    public Vamreg $controller;
    public VamregModelsRecordingClient $client;

    /** @var list<string> PHP warnings raised inside capture() */
    public array $warnings = [];

    public function __construct(array $conf = [], ?VamregModelsRecordingClient $client = null)
    {
        require_once APPPATH . 'controllers/Vamreg.php';

        $ci = get_instance();
        foreach ([
            'Products_model' => 'products', 'Vamreg_index_model' => 'vamreg_index',
            'Vamreg_in_buffer_model' => 'vamreg_in', 'Vamreg_out_buffer_model' => 'vamreg_out',
        ] as $model => $alias) {
            isset($ci->{$alias}) || $ci->load->model($model, $alias);
        }
        $ci->load->helper(['quarter', 'cnk', 'vamreg']);
        $ci->load->library('vamreg_sync');

        $this->client = $client ?? new VamregModelsRecordingClient();
        // the loader skips a library that already exists as a property of the instance
        $ci->vamregclient = $this->client;

        $this->controller = (new ReflectionClass(Vamreg::class))->newInstanceWithoutConstructor();
        foreach (get_object_vars($ci) as $name => $value) {
            $this->controller->{$name} = $value;
        }
        $this->controller->conf = $conf + ['vamreg_api_key' => ['value' => base64_encode('test-key')]];
        $this->controller->key = 'test-key';
    }

    public static function setting(string $value): array
    {
        return ['value' => base64_encode($value), 'updated_at' => null, 'created_at' => null];
    }

    /** The JSON body the controller answered, and clears the output. */
    public function answer(): array
    {
        $out = get_instance()->output;
        $body = json_decode((string) $out->get_output(), true);
        $out->set_output('');

        return $body ?? [];
    }

    /** Calls a method that echoes (reset) and returns what it printed. */
    public function capture(callable $call): string
    {
        $this->warnings = [];
        set_error_handler(function (int $no, string $msg): bool {
            if ($no === E_WARNING) {
                $this->warnings[] = $msg;
                return true;
            }
            return false;
        });
        ob_start();
        try {
            $call();
        } finally {
            $printed = (string) ob_get_clean();
            restore_error_handler();
        }

        return $printed;
    }

    public function release(): void
    {
        $ci = get_instance();
        unset($ci->vamregclient);
        $ci->output->set_output('');
    }
}
