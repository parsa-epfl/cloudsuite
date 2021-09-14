weakmap-polyfill
================

[![NPM Version](https://img.shields.io/npm/v/weakmap-polyfill.svg)](https://www.npmjs.com/package/weakmap-polyfill)
[![Build Status](https://travis-ci.org/polygonplanet/weakmap-polyfill.svg?branch=master)](https://travis-ci.org/polygonplanet/weakmap-polyfill)
[![Bundle Size (minified)](https://img.shields.io/github/size/polygonplanet/weakmap-polyfill/weakmap-polyfill.min.js.svg)](https://github.com/polygonplanet/weakmap-polyfill/blob/master/weakmap-polyfill.min.js)
[![GitHub License](https://img.shields.io/github/license/polygonplanet/weakmap-polyfill.svg)](https://github.com/polygonplanet/weakmap-polyfill/blob/master/LICENSE)

[ECMAScript6 WeakMap](http://www.ecma-international.org/ecma-262/6.0/#sec-weakmap-objects) polyfill.

## Installation

### npm

```bash
$ npm install weakmap-polyfill
```

### Usage

Import or require `weakmap-polyfill`, then **WeakMap** will be defined in the global scope if native WeakMap is not supported in running environment.

#### node

```javascript
require('weakmap-polyfill');
var weakMap = new WeakMap();
```

#### webpack etc.

```javascript
import 'weakmap-polyfill';
const weakMap = new WeakMap();
```

#### browser (standalone)

```html
<script src="weakmap-polyfill.min.js"></script>
<script>
var weakMap = new WeakMap();
</script>
```

## License

MIT
