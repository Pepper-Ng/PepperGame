#!/bin/sh

# Reuse existing compiled libraries when Rust tooling is unavailable in runtime images.
if ! command -v cargo >/dev/null 2>&1; then
    if [ -f storage/rust-libs/libbattle_engine_ffi.so ]; then
        echo "Cargo not found; reusing existing Rust libraries from storage/rust-libs"
        exit 0
    fi

    echo "ERROR: cargo is not installed and no precompiled Rust libraries were found!"
    exit 1
fi

# Compile the rust workspace
echo "Compiling Rust workspace..."
if ! cargo build "--manifest-path=rust/Cargo.toml" "--release"; then
    echo "ERROR: Rust compilation failed!"
    exit 1
fi

# Copy the compiled rust libraries to the storage/rust-libs directory.
# The .so files are called by Laravel.
if [ -f rust/target/release/libbattle_engine_ffi.so ]; then
    cp rust/target/release/libbattle_engine_ffi.so storage/rust-libs/
    echo "Copied libbattle_engine_ffi.so"
else
    echo "ERROR: libbattle_engine_ffi.so not found after compilation!"
    exit 1
fi

if [ -f rust/target/release/libtest_ffi.so ]; then
    cp rust/target/release/libtest_ffi.so storage/rust-libs/
    echo "Copied libtest_ffi.so"
fi

echo "Rust compilation completed successfully!"
