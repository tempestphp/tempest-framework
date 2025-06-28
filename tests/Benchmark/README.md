# Tempest benchmarks

Tempest uses phpbench to benchmark performance critical benchmarks. These are not required to pass any requirements, but
can be used as a tool during optimization efforts.

## Usage

Run in the repository root using:

```shell
./vendor/bin/phpbench run tests/Benchmark --report=aggregate --iterations=5
```

Iterations can be used to alter how many times all benchmarks are ran.
