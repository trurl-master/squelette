# Squelette

Squelette skeleton application

## Installation

Node, npm and composer are required

1. `npm install`
2. `npm run squelette init -- -c`
3. `npm run squelette refresh db-config`

## Usage

### Working with db schema

Migration: `npm run squelette diff` then `npm run squelette migrate`  
Reverting latest migration: `npm run squelette migrate-back`  
Be sure to update models after migration: `npm run squelette refresh db-model`

### Building assets

In dev mode: `npm run dev`  
Watch in dev mode: `npm run watch`  
In production mode: `npm run build`
