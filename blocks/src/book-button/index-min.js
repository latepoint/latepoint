"use strict";

var _blocks = require("@wordpress/blocks");
var _edit = _interopRequireDefault(require("./edit"));
var _save = _interopRequireDefault(require("./save"));
var _block = _interopRequireDefault(require("./block.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */

/**
 * Internal dependencies
 */

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */

let test = (0, _blocks.registerBlockType)(_block.default.name, {
  edit: _edit.default,
  save: _save.default
});

//# sourceMappingURL=index-min.js.map
