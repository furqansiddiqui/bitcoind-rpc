# Bitcoin RPC

RPC client for Bitcoin (and other altcoins based on bitcoind)

## Why Bitcoin RPC?

* Perfect OOP API for calling bitcoind RPC methods
* Fully validated requests
* Fully validated response objects
* Ability to use custom "relay" client for communication with daemon (i.e. Create your own relay with AES encryption for working with nodes in your LAN or even internet)
* Identify exactly what coin node/daemon is running
* Support dynamic wallets loading/unloading on bitcoind > 0.17.0
* Events triggers (on wallet load/unload, unlock, etc...)

## Prerequisites

* PHP >= 7.1
* [furqansiddiqui/http-client](https://github.com/furqansiddiqui/http-client) >= v0.4.1

## Installation

`composer require furqansiddiqui/bitcoind-rpc`