<?php

declare(strict_types=1);

final class TestReporter
{
    private int $passed = 0;
    private int $failed = 0;
    private int $blocked = 0;

    public function pass(string $name): void
    {
        $this->passed++;
        $this->write('PASS', $name);
    }

    public function fail(string $name, string $reason): void
    {
        $this->failed++;
        $this->write('FAIL', $name, $reason);
    }

    public function block(string $name, string $reason): void
    {
        $this->blocked++;
        $this->write('BLOCKED', $name, $reason);
    }

    public function check(string $name, bool $condition, string $reason): void
    {
        if ($condition) {
            $this->pass($name);
            return;
        }

        $this->fail($name, $reason);
    }

    public function summary(): int
    {
        echo PHP_EOL . 'TỔNG KẾT: ';
        echo 'PASS=' . $this->passed;
        echo ' FAIL=' . $this->failed;
        echo ' BLOCKED=' . $this->blocked . PHP_EOL;

        if ($this->failed > 0) {
            return 1;
        }

        if ($this->blocked > 0) {
            return 2;
        }

        return 0;
    }

    private function write(string $status, string $name, string $reason = ''): void
    {
        echo '[' . $status . '] ' . $name;

        if ($reason !== '') {
            echo ': ' . $reason;
        }

        echo PHP_EOL;
    }
}
