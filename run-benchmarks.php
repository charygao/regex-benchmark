<?php

const PATTERNS_COUNT = 3;

const RUN_TIMES = 10;

const BUILDS = [
    'C PCRE2'      => 'gcc -O3 -DNDEBUG c/benchmark.c -I/usr/local/include/ -lpcre2-8 -o c/bin/benchmark',
    'Crystal'      => 'crystal build crystal/benchmark.cr --release -o crystal/bin/benchmark',
    'C# Mono'      => 'mcs csharp/benchmark.cs -out:csharp/bin-mono/benchmark.exe -debug- -optimize',
    'C# .Net Core' => 'dotnet build csharp/benchmark.csproj -c Release',
    'D dmd'        => 'dmd -O -release -inline -of=d/bin/benchmark d/benchmark.d',
    'D ldc'        => 'ldc2 -O3 -release -of=d/bin/benchmark-ldc d/benchmark.d',
    'Go'           => 'go build -ldflags "-s -w" -o go/bin/benchmark ./go',
    'Java'         => 'javac java/Benchmark.java',
    'Kotlin'       => 'kotlinc kotlin/benchmark.kt -include-runtime -d kotlin/benchmark.jar',
    'Rust'         => 'cargo build --quiet --release --manifest-path=rust/Cargo.toml',
];

const COMMANDS = [
    'C PCRE2'      => 'c/bin/benchmark',
    'Crystal'      => 'crystal/bin/benchmark',
    'C# Mono'      => 'mono -O=all csharp/bin-mono/benchmark.exe',
    'C# .Net Core' => 'dotnet csharp/bin/Release/netcoreapp2.0/benchmark.dll',
    'D dmd'        => 'd/bin/benchmark',
    'D ldc'        => 'd/bin/benchmark-ldc',
    'Go'           => 'go/bin/benchmark',
    'Java'         => 'java -classpath java Benchmark',
    'Javascript'   => 'node javascript/benchmark.js',
    'Kotlin'       => 'kotlin kotlin/benchmark.jar',
    'Perl'         => 'perl perl/benchmark.pl',
    'PHP'          => 'php php/benchmark.php',
    'Python 2'     => 'python python/benchmark.py',
    'Python 3'     => 'python3 python/benchmark.py',
    'Python PyPy'  => 'pypy python/benchmark.py',
    'Ruby'         => 'ruby ruby/benchmark.rb',
    'Rust'         => 'rust/target/release/benchmark',
];

echo '- Build' . PHP_EOL;

foreach (BUILDS as $language => $buildCmd) {
    shell_exec($buildCmd);

    echo $language . ' built.' . PHP_EOL;
}

echo PHP_EOL . '- Run' . PHP_EOL;

$results = [];

foreach (COMMANDS as $language => $command) {
    $currentResults = [];

    for ($i = 0; $i < RUN_TIMES; $i++) {
        $out = shell_exec($command . ' input-text.txt');
        preg_match_all('/^\d+\.\d+/m', $out, $matches);

        for ($j = 0; $j < PATTERNS_COUNT; $j++) {
            $currentResults[$j][] = $matches[0][$j];
        }
    }

    for ($i = 0; $i < PATTERNS_COUNT; $i++) {
        $results[$language][] = array_sum($currentResults[$i]) / count($currentResults[$i]);
    }

    $results[$language][PATTERNS_COUNT] = array_sum($results[$language]);

    echo $language . ' ran.' . PHP_EOL;
}

echo PHP_EOL . '- Results' . PHP_EOL;

uasort($results, function ($a, $b) {
    return $a[PATTERNS_COUNT] < $b[PATTERNS_COUNT] ? -1 : 1;
});

$results = array_walk($results, function ($result, $language) {
    $result = array_map(function ($time) {
        return number_format($time, 2, '.', '');
    }, $result);

    echo '**' . $language . '** | ' . implode(' | ', $result) . PHP_EOL;
});
