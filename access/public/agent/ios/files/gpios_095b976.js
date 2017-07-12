;/*!js/zepto.js*/
/* Zepto v1.2.0 - zepto event ajax form ie - zeptojs.com/license */
(function(global, factory) {
  if (typeof define === 'function' && define.amd)
    define(function() { return factory(global) })
  else
    factory(global)
}(this, function(window) {
  var Zepto = (function() {
  var undefined, key, $, classList, emptyArray = [], concat = emptyArray.concat, filter = emptyArray.filter, slice = emptyArray.slice,
    document = window.document,
    elementDisplay = {}, classCache = {},
    cssNumber = { 'column-count': 1, 'columns': 1, 'font-weight': 1, 'line-height': 1,'opacity': 1, 'z-index': 1, 'zoom': 1 },
    fragmentRE = /^\s*<(\w+|!)[^>]*>/,
    singleTagRE = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
    tagExpanderRE = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/ig,
    rootNodeRE = /^(?:body|html)$/i,
    capitalRE = /([A-Z])/g,

    // special attributes that should be get/set via method calls
    methodAttributes = ['val', 'css', 'html', 'text', 'data', 'width', 'height', 'offset'],

    adjacencyOperators = [ 'after', 'prepend', 'before', 'append' ],
    table = document.createElement('table'),
    tableRow = document.createElement('tr'),
    containers = {
      'tr': document.createElement('tbody'),
      'tbody': table, 'thead': table, 'tfoot': table,
      'td': tableRow, 'th': tableRow,
      '*': document.createElement('div')
    },
    readyRE = /complete|loaded|interactive/,
    simpleSelectorRE = /^[\w-]*$/,
    class2type = {},
    toString = class2type.toString,
    zepto = {},
    camelize, uniq,
    tempParent = document.createElement('div'),
    propMap = {
      'tabindex': 'tabIndex',
      'readonly': 'readOnly',
      'for': 'htmlFor',
      'class': 'className',
      'maxlength': 'maxLength',
      'cellspacing': 'cellSpacing',
      'cellpadding': 'cellPadding',
      'rowspan': 'rowSpan',
      'colspan': 'colSpan',
      'usemap': 'useMap',
      'frameborder': 'frameBorder',
      'contenteditable': 'contentEditable'
    },
    isArray = Array.isArray ||
      function(object){ return object instanceof Array }

  zepto.matches = function(element, selector) {
    if (!selector || !element || element.nodeType !== 1) return false
    var matchesSelector = element.matches || element.webkitMatchesSelector ||
                          element.mozMatchesSelector || element.oMatchesSelector ||
                          element.matchesSelector
    if (matchesSelector) return matchesSelector.call(element, selector)
    // fall back to performing a selector:
    var match, parent = element.parentNode, temp = !parent
    if (temp) (parent = tempParent).appendChild(element)
    match = ~zepto.qsa(parent, selector).indexOf(element)
    temp && tempParent.removeChild(element)
    return match
  }

  function type(obj) {
    return obj == null ? String(obj) :
      class2type[toString.call(obj)] || "object"
  }

  function isFunction(value) { return type(value) == "function" }
  function isWindow(obj)     { return obj != null && obj == obj.window }
  function isDocument(obj)   { return obj != null && obj.nodeType == obj.DOCUMENT_NODE }
  function isObject(obj)     { return type(obj) == "object" }
  function isPlainObject(obj) {
    return isObject(obj) && !isWindow(obj) && Object.getPrototypeOf(obj) == Object.prototype
  }

  function likeArray(obj) {
    var length = !!obj && 'length' in obj && obj.length,
      type = $.type(obj)

    return 'function' != type && !isWindow(obj) && (
      'array' == type || length === 0 ||
        (typeof length == 'number' && length > 0 && (length - 1) in obj)
    )
  }

  function compact(array) { return filter.call(array, function(item){ return item != null }) }
  function flatten(array) { return array.length > 0 ? $.fn.concat.apply([], array) : array }
  camelize = function(str){ return str.replace(/-+(.)?/g, function(match, chr){ return chr ? chr.toUpperCase() : '' }) }
  function dasherize(str) {
    return str.replace(/::/g, '/')
           .replace(/([A-Z]+)([A-Z][a-z])/g, '$1_$2')
           .replace(/([a-z\d])([A-Z])/g, '$1_$2')
           .replace(/_/g, '-')
           .toLowerCase()
  }
  uniq = function(array){ return filter.call(array, function(item, idx){ return array.indexOf(item) == idx }) }

  function classRE(name) {
    return name in classCache ?
      classCache[name] : (classCache[name] = new RegExp('(^|\\s)' + name + '(\\s|$)'))
  }

  function maybeAddPx(name, value) {
    return (typeof value == "number" && !cssNumber[dasherize(name)]) ? value + "px" : value
  }

  function defaultDisplay(nodeName) {
    var element, display
    if (!elementDisplay[nodeName]) {
      element = document.createElement(nodeName)
      document.body.appendChild(element)
      display = getComputedStyle(element, '').getPropertyValue("display")
      element.parentNode.removeChild(element)
      display == "none" && (display = "block")
      elementDisplay[nodeName] = display
    }
    return elementDisplay[nodeName]
  }

  function children(element) {
    return 'children' in element ?
      slice.call(element.children) :
      $.map(element.childNodes, function(node){ if (node.nodeType == 1) return node })
  }

  function Z(dom, selector) {
    var i, len = dom ? dom.length : 0
    for (i = 0; i < len; i++) this[i] = dom[i]
    this.length = len
    this.selector = selector || ''
  }

  // `$.zepto.fragment` takes a html string and an optional tag name
  // to generate DOM nodes from the given html string.
  // The generated DOM nodes are returned as an array.
  // This function can be overridden in plugins for example to make
  // it compatible with browsers that don't support the DOM fully.
  zepto.fragment = function(html, name, properties) {
    var dom, nodes, container

    // A special case optimization for a single tag
    if (singleTagRE.test(html)) dom = $(document.createElement(RegExp.$1))

    if (!dom) {
      if (html.replace) html = html.replace(tagExpanderRE, "<$1></$2>")
      if (name === undefined) name = fragmentRE.test(html) && RegExp.$1
      if (!(name in containers)) name = '*'

      container = containers[name]
      container.innerHTML = '' + html
      dom = $.each(slice.call(container.childNodes), function(){
        container.removeChild(this)
      })
    }

    if (isPlainObject(properties)) {
      nodes = $(dom)
      $.each(properties, function(key, value) {
        if (methodAttributes.indexOf(key) > -1) nodes[key](value)
        else nodes.attr(key, value)
      })
    }

    return dom
  }

  // `$.zepto.Z` swaps out the prototype of the given `dom` array
  // of nodes with `$.fn` and thus supplying all the Zepto functions
  // to the array. This method can be overridden in plugins.
  zepto.Z = function(dom, selector) {
    return new Z(dom, selector)
  }

  // `$.zepto.isZ` should return `true` if the given object is a Zepto
  // collection. This method can be overridden in plugins.
  zepto.isZ = function(object) {
    return object instanceof zepto.Z
  }

  // `$.zepto.init` is Zepto's counterpart to jQuery's `$.fn.init` and
  // takes a CSS selector and an optional context (and handles various
  // special cases).
  // This method can be overridden in plugins.
  zepto.init = function(selector, context) {
    var dom
    // If nothing given, return an empty Zepto collection
    if (!selector) return zepto.Z()
    // Optimize for string selectors
    else if (typeof selector == 'string') {
      selector = selector.trim()
      // If it's a html fragment, create nodes from it
      // Note: In both Chrome 21 and Firefox 15, DOM error 12
      // is thrown if the fragment doesn't begin with <
      if (selector[0] == '<' && fragmentRE.test(selector))
        dom = zepto.fragment(selector, RegExp.$1, context), selector = null
      // If there's a context, create a collection on that context first, and select
      // nodes from there
      else if (context !== undefined) return $(context).find(selector)
      // If it's a CSS selector, use it to select nodes.
      else dom = zepto.qsa(document, selector)
    }
    // If a function is given, call it when the DOM is ready
    else if (isFunction(selector)) return $(document).ready(selector)
    // If a Zepto collection is given, just return it
    else if (zepto.isZ(selector)) return selector
    else {
      // normalize array if an array of nodes is given
      if (isArray(selector)) dom = compact(selector)
      // Wrap DOM nodes.
      else if (isObject(selector))
        dom = [selector], selector = null
      // If it's a html fragment, create nodes from it
      else if (fragmentRE.test(selector))
        dom = zepto.fragment(selector.trim(), RegExp.$1, context), selector = null
      // If there's a context, create a collection on that context first, and select
      // nodes from there
      else if (context !== undefined) return $(context).find(selector)
      // And last but no least, if it's a CSS selector, use it to select nodes.
      else dom = zepto.qsa(document, selector)
    }
    // create a new Zepto collection from the nodes found
    return zepto.Z(dom, selector)
  }

  // `$` will be the base `Zepto` object. When calling this
  // function just call `$.zepto.init, which makes the implementation
  // details of selecting nodes and creating Zepto collections
  // patchable in plugins.
  $ = function(selector, context){
    return zepto.init(selector, context)
  }

  function extend(target, source, deep) {
    for (key in source)
      if (deep && (isPlainObject(source[key]) || isArray(source[key]))) {
        if (isPlainObject(source[key]) && !isPlainObject(target[key]))
          target[key] = {}
        if (isArray(source[key]) && !isArray(target[key]))
          target[key] = []
        extend(target[key], source[key], deep)
      }
      else if (source[key] !== undefined) target[key] = source[key]
  }

  // Copy all but undefined properties from one or more
  // objects to the `target` object.
  $.extend = function(target){
    var deep, args = slice.call(arguments, 1)
    if (typeof target == 'boolean') {
      deep = target
      target = args.shift()
    }
    args.forEach(function(arg){ extend(target, arg, deep) })
    return target
  }

  // `$.zepto.qsa` is Zepto's CSS selector implementation which
  // uses `document.querySelectorAll` and optimizes for some special cases, like `#id`.
  // This method can be overridden in plugins.
  zepto.qsa = function(element, selector){
    var found,
        maybeID = selector[0] == '#',
        maybeClass = !maybeID && selector[0] == '.',
        nameOnly = maybeID || maybeClass ? selector.slice(1) : selector, // Ensure that a 1 char tag name still gets checked
        isSimple = simpleSelectorRE.test(nameOnly)
    return (element.getElementById && isSimple && maybeID) ? // Safari DocumentFragment doesn't have getElementById
      ( (found = element.getElementById(nameOnly)) ? [found] : [] ) :
      (element.nodeType !== 1 && element.nodeType !== 9 && element.nodeType !== 11) ? [] :
      slice.call(
        isSimple && !maybeID && element.getElementsByClassName ? // DocumentFragment doesn't have getElementsByClassName/TagName
          maybeClass ? element.getElementsByClassName(nameOnly) : // If it's simple, it could be a class
          element.getElementsByTagName(selector) : // Or a tag
          element.querySelectorAll(selector) // Or it's not simple, and we need to query all
      )
  }

  function filtered(nodes, selector) {
    return selector == null ? $(nodes) : $(nodes).filter(selector)
  }

  $.contains = document.documentElement.contains ?
    function(parent, node) {
      return parent !== node && parent.contains(node)
    } :
    function(parent, node) {
      while (node && (node = node.parentNode))
        if (node === parent) return true
      return false
    }

  function funcArg(context, arg, idx, payload) {
    return isFunction(arg) ? arg.call(context, idx, payload) : arg
  }

  function setAttribute(node, name, value) {
    value == null ? node.removeAttribute(name) : node.setAttribute(name, value)
  }

  // access className property while respecting SVGAnimatedString
  function className(node, value){
    var klass = node.className || '',
        svg   = klass && klass.baseVal !== undefined

    if (value === undefined) return svg ? klass.baseVal : klass
    svg ? (klass.baseVal = value) : (node.className = value)
  }

  // "true"  => true
  // "false" => false
  // "null"  => null
  // "42"    => 42
  // "42.5"  => 42.5
  // "08"    => "08"
  // JSON    => parse if valid
  // String  => self
  function deserializeValue(value) {
    try {
      return value ?
        value == "true" ||
        ( value == "false" ? false :
          value == "null" ? null :
          +value + "" == value ? +value :
          /^[\[\{]/.test(value) ? $.parseJSON(value) :
          value )
        : value
    } catch(e) {
      return value
    }
  }

  $.type = type
  $.isFunction = isFunction
  $.isWindow = isWindow
  $.isArray = isArray
  $.isPlainObject = isPlainObject

  $.isEmptyObject = function(obj) {
    var name
    for (name in obj) return false
    return true
  }

  $.isNumeric = function(val) {
    var num = Number(val), type = typeof val
    return val != null && type != 'boolean' &&
      (type != 'string' || val.length) &&
      !isNaN(num) && isFinite(num) || false
  }

  $.inArray = function(elem, array, i){
    return emptyArray.indexOf.call(array, elem, i)
  }

  $.camelCase = camelize
  $.trim = function(str) {
    return str == null ? "" : String.prototype.trim.call(str)
  }

  // plugin compatibility
  $.uuid = 0
  $.support = { }
  $.expr = { }
  $.noop = function() {}

  $.map = function(elements, callback){
    var value, values = [], i, key
    if (likeArray(elements))
      for (i = 0; i < elements.length; i++) {
        value = callback(elements[i], i)
        if (value != null) values.push(value)
      }
    else
      for (key in elements) {
        value = callback(elements[key], key)
        if (value != null) values.push(value)
      }
    return flatten(values)
  }

  $.each = function(elements, callback){
    var i, key
    if (likeArray(elements)) {
      for (i = 0; i < elements.length; i++)
        if (callback.call(elements[i], i, elements[i]) === false) return elements
    } else {
      for (key in elements)
        if (callback.call(elements[key], key, elements[key]) === false) return elements
    }

    return elements
  }

  $.grep = function(elements, callback){
    return filter.call(elements, callback)
  }

  if (window.JSON) $.parseJSON = JSON.parse

  // Populate the class2type map
  $.each("Boolean Number String Function Array Date RegExp Object Error".split(" "), function(i, name) {
    class2type[ "[object " + name + "]" ] = name.toLowerCase()
  })

  // Define methods that will be available on all
  // Zepto collections
  $.fn = {
    constructor: zepto.Z,
    length: 0,

    // Because a collection acts like an array
    // copy over these useful array functions.
    forEach: emptyArray.forEach,
    reduce: emptyArray.reduce,
    push: emptyArray.push,
    sort: emptyArray.sort,
    splice: emptyArray.splice,
    indexOf: emptyArray.indexOf,
    concat: function(){
      var i, value, args = []
      for (i = 0; i < arguments.length; i++) {
        value = arguments[i]
        args[i] = zepto.isZ(value) ? value.toArray() : value
      }
      return concat.apply(zepto.isZ(this) ? this.toArray() : this, args)
    },

    // `map` and `slice` in the jQuery API work differently
    // from their array counterparts
    map: function(fn){
      return $($.map(this, function(el, i){ return fn.call(el, i, el) }))
    },
    slice: function(){
      return $(slice.apply(this, arguments))
    },

    ready: function(callback){
      // need to check if document.body exists for IE as that browser reports
      // document ready when it hasn't yet created the body element
      if (readyRE.test(document.readyState) && document.body) callback($)
      else document.addEventListener('DOMContentLoaded', function(){ callback($) }, false)
      return this
    },
    get: function(idx){
      return idx === undefined ? slice.call(this) : this[idx >= 0 ? idx : idx + this.length]
    },
    toArray: function(){ return this.get() },
    size: function(){
      return this.length
    },
    remove: function(){
      return this.each(function(){
        if (this.parentNode != null)
          this.parentNode.removeChild(this)
      })
    },
    each: function(callback){
      emptyArray.every.call(this, function(el, idx){
        return callback.call(el, idx, el) !== false
      })
      return this
    },
    filter: function(selector){
      if (isFunction(selector)) return this.not(this.not(selector))
      return $(filter.call(this, function(element){
        return zepto.matches(element, selector)
      }))
    },
    add: function(selector,context){
      return $(uniq(this.concat($(selector,context))))
    },
    is: function(selector){
      return this.length > 0 && zepto.matches(this[0], selector)
    },
    not: function(selector){
      var nodes=[]
      if (isFunction(selector) && selector.call !== undefined)
        this.each(function(idx){
          if (!selector.call(this,idx)) nodes.push(this)
        })
      else {
        var excludes = typeof selector == 'string' ? this.filter(selector) :
          (likeArray(selector) && isFunction(selector.item)) ? slice.call(selector) : $(selector)
        this.forEach(function(el){
          if (excludes.indexOf(el) < 0) nodes.push(el)
        })
      }
      return $(nodes)
    },
    has: function(selector){
      return this.filter(function(){
        return isObject(selector) ?
          $.contains(this, selector) :
          $(this).find(selector).size()
      })
    },
    eq: function(idx){
      return idx === -1 ? this.slice(idx) : this.slice(idx, + idx + 1)
    },
    first: function(){
      var el = this[0]
      return el && !isObject(el) ? el : $(el)
    },
    last: function(){
      var el = this[this.length - 1]
      return el && !isObject(el) ? el : $(el)
    },
    find: function(selector){
      var result, $this = this
      if (!selector) result = $()
      else if (typeof selector == 'object')
        result = $(selector).filter(function(){
          var node = this
          return emptyArray.some.call($this, function(parent){
            return $.contains(parent, node)
          })
        })
      else if (this.length == 1) result = $(zepto.qsa(this[0], selector))
      else result = this.map(function(){ return zepto.qsa(this, selector) })
      return result
    },
    closest: function(selector, context){
      var nodes = [], collection = typeof selector == 'object' && $(selector)
      this.each(function(_, node){
        while (node && !(collection ? collection.indexOf(node) >= 0 : zepto.matches(node, selector)))
          node = node !== context && !isDocument(node) && node.parentNode
        if (node && nodes.indexOf(node) < 0) nodes.push(node)
      })
      return $(nodes)
    },
    parents: function(selector){
      var ancestors = [], nodes = this
      while (nodes.length > 0)
        nodes = $.map(nodes, function(node){
          if ((node = node.parentNode) && !isDocument(node) && ancestors.indexOf(node) < 0) {
            ancestors.push(node)
            return node
          }
        })
      return filtered(ancestors, selector)
    },
    parent: function(selector){
      return filtered(uniq(this.pluck('parentNode')), selector)
    },
    children: function(selector){
      return filtered(this.map(function(){ return children(this) }), selector)
    },
    contents: function() {
      return this.map(function() { return this.contentDocument || slice.call(this.childNodes) })
    },
    siblings: function(selector){
      return filtered(this.map(function(i, el){
        return filter.call(children(el.parentNode), function(child){ return child!==el })
      }), selector)
    },
    empty: function(){
      return this.each(function(){ this.innerHTML = '' })
    },
    // `pluck` is borrowed from Prototype.js
    pluck: function(property){
      return $.map(this, function(el){ return el[property] })
    },
    show: function(){
      return this.each(function(){
        this.style.display == "none" && (this.style.display = '')
        if (getComputedStyle(this, '').getPropertyValue("display") == "none")
          this.style.display = defaultDisplay(this.nodeName)
      })
    },
    replaceWith: function(newContent){
      return this.before(newContent).remove()
    },
    wrap: function(structure){
      var func = isFunction(structure)
      if (this[0] && !func)
        var dom   = $(structure).get(0),
            clone = dom.parentNode || this.length > 1

      return this.each(function(index){
        $(this).wrapAll(
          func ? structure.call(this, index) :
            clone ? dom.cloneNode(true) : dom
        )
      })
    },
    wrapAll: function(structure){
      if (this[0]) {
        $(this[0]).before(structure = $(structure))
        var children
        // drill down to the inmost element
        while ((children = structure.children()).length) structure = children.first()
        $(structure).append(this)
      }
      return this
    },
    wrapInner: function(structure){
      var func = isFunction(structure)
      return this.each(function(index){
        var self = $(this), contents = self.contents(),
            dom  = func ? structure.call(this, index) : structure
        contents.length ? contents.wrapAll(dom) : self.append(dom)
      })
    },
    unwrap: function(){
      this.parent().each(function(){
        $(this).replaceWith($(this).children())
      })
      return this
    },
    clone: function(){
      return this.map(function(){ return this.cloneNode(true) })
    },
    hide: function(){
      return this.css("display", "none")
    },
    toggle: function(setting){
      return this.each(function(){
        var el = $(this)
        ;(setting === undefined ? el.css("display") == "none" : setting) ? el.show() : el.hide()
      })
    },
    prev: function(selector){ return $(this.pluck('previousElementSibling')).filter(selector || '*') },
    next: function(selector){ return $(this.pluck('nextElementSibling')).filter(selector || '*') },
    html: function(html){
      return 0 in arguments ?
        this.each(function(idx){
          var originHtml = this.innerHTML
          $(this).empty().append( funcArg(this, html, idx, originHtml) )
        }) :
        (0 in this ? this[0].innerHTML : null)
    },
    text: function(text){
      return 0 in arguments ?
        this.each(function(idx){
          var newText = funcArg(this, text, idx, this.textContent)
          this.textContent = newText == null ? '' : ''+newText
        }) :
        (0 in this ? this.pluck('textContent').join("") : null)
    },
    attr: function(name, value){
      var result
      return (typeof name == 'string' && !(1 in arguments)) ?
        (0 in this && this[0].nodeType == 1 && (result = this[0].getAttribute(name)) != null ? result : undefined) :
        this.each(function(idx){
          if (this.nodeType !== 1) return
          if (isObject(name)) for (key in name) setAttribute(this, key, name[key])
          else setAttribute(this, name, funcArg(this, value, idx, this.getAttribute(name)))
        })
    },
    removeAttr: function(name){
      return this.each(function(){ this.nodeType === 1 && name.split(' ').forEach(function(attribute){
        setAttribute(this, attribute)
      }, this)})
    },
    prop: function(name, value){
      name = propMap[name] || name
      return (1 in arguments) ?
        this.each(function(idx){
          this[name] = funcArg(this, value, idx, this[name])
        }) :
        (this[0] && this[0][name])
    },
    removeProp: function(name){
      name = propMap[name] || name
      return this.each(function(){ delete this[name] })
    },
    data: function(name, value){
      var attrName = 'data-' + name.replace(capitalRE, '-$1').toLowerCase()

      var data = (1 in arguments) ?
        this.attr(attrName, value) :
        this.attr(attrName)

      return data !== null ? deserializeValue(data) : undefined
    },
    val: function(value){
      if (0 in arguments) {
        if (value == null) value = ""
        return this.each(function(idx){
          this.value = funcArg(this, value, idx, this.value)
        })
      } else {
        return this[0] && (this[0].multiple ?
           $(this[0]).find('option').filter(function(){ return this.selected }).pluck('value') :
           this[0].value)
      }
    },
    offset: function(coordinates){
      if (coordinates) return this.each(function(index){
        var $this = $(this),
            coords = funcArg(this, coordinates, index, $this.offset()),
            parentOffset = $this.offsetParent().offset(),
            props = {
              top:  coords.top  - parentOffset.top,
              left: coords.left - parentOffset.left
            }

        if ($this.css('position') == 'static') props['position'] = 'relative'
        $this.css(props)
      })
      if (!this.length) return null
      if (document.documentElement !== this[0] && !$.contains(document.documentElement, this[0]))
        return {top: 0, left: 0}
      var obj = this[0].getBoundingClientRect()
      return {
        left: obj.left + window.pageXOffset,
        top: obj.top + window.pageYOffset,
        width: Math.round(obj.width),
        height: Math.round(obj.height)
      }
    },
    css: function(property, value){
      if (arguments.length < 2) {
        var element = this[0]
        if (typeof property == 'string') {
          if (!element) return
          return element.style[camelize(property)] || getComputedStyle(element, '').getPropertyValue(property)
        } else if (isArray(property)) {
          if (!element) return
          var props = {}
          var computedStyle = getComputedStyle(element, '')
          $.each(property, function(_, prop){
            props[prop] = (element.style[camelize(prop)] || computedStyle.getPropertyValue(prop))
          })
          return props
        }
      }

      var css = ''
      if (type(property) == 'string') {
        if (!value && value !== 0)
          this.each(function(){ this.style.removeProperty(dasherize(property)) })
        else
          css = dasherize(property) + ":" + maybeAddPx(property, value)
      } else {
        for (key in property)
          if (!property[key] && property[key] !== 0)
            this.each(function(){ this.style.removeProperty(dasherize(key)) })
          else
            css += dasherize(key) + ':' + maybeAddPx(key, property[key]) + ';'
      }

      return this.each(function(){ this.style.cssText += ';' + css })
    },
    index: function(element){
      return element ? this.indexOf($(element)[0]) : this.parent().children().indexOf(this[0])
    },
    hasClass: function(name){
      if (!name) return false
      return emptyArray.some.call(this, function(el){
        return this.test(className(el))
      }, classRE(name))
    },
    addClass: function(name){
      if (!name) return this
      return this.each(function(idx){
        if (!('className' in this)) return
        classList = []
        var cls = className(this), newName = funcArg(this, name, idx, cls)
        newName.split(/\s+/g).forEach(function(klass){
          if (!$(this).hasClass(klass)) classList.push(klass)
        }, this)
        classList.length && className(this, cls + (cls ? " " : "") + classList.join(" "))
      })
    },
    removeClass: function(name){
      return this.each(function(idx){
        if (!('className' in this)) return
        if (name === undefined) return className(this, '')
        classList = className(this)
        funcArg(this, name, idx, classList).split(/\s+/g).forEach(function(klass){
          classList = classList.replace(classRE(klass), " ")
        })
        className(this, classList.trim())
      })
    },
    toggleClass: function(name, when){
      if (!name) return this
      return this.each(function(idx){
        var $this = $(this), names = funcArg(this, name, idx, className(this))
        names.split(/\s+/g).forEach(function(klass){
          (when === undefined ? !$this.hasClass(klass) : when) ?
            $this.addClass(klass) : $this.removeClass(klass)
        })
      })
    },
    scrollTop: function(value){
      if (!this.length) return
      var hasScrollTop = 'scrollTop' in this[0]
      if (value === undefined) return hasScrollTop ? this[0].scrollTop : this[0].pageYOffset
      return this.each(hasScrollTop ?
        function(){ this.scrollTop = value } :
        function(){ this.scrollTo(this.scrollX, value) })
    },
    scrollLeft: function(value){
      if (!this.length) return
      var hasScrollLeft = 'scrollLeft' in this[0]
      if (value === undefined) return hasScrollLeft ? this[0].scrollLeft : this[0].pageXOffset
      return this.each(hasScrollLeft ?
        function(){ this.scrollLeft = value } :
        function(){ this.scrollTo(value, this.scrollY) })
    },
    position: function() {
      if (!this.length) return

      var elem = this[0],
        // Get *real* offsetParent
        offsetParent = this.offsetParent(),
        // Get correct offsets
        offset       = this.offset(),
        parentOffset = rootNodeRE.test(offsetParent[0].nodeName) ? { top: 0, left: 0 } : offsetParent.offset()

      // Subtract element margins
      // note: when an element has margin: auto the offsetLeft and marginLeft
      // are the same in Safari causing offset.left to incorrectly be 0
      offset.top  -= parseFloat( $(elem).css('margin-top') ) || 0
      offset.left -= parseFloat( $(elem).css('margin-left') ) || 0

      // Add offsetParent borders
      parentOffset.top  += parseFloat( $(offsetParent[0]).css('border-top-width') ) || 0
      parentOffset.left += parseFloat( $(offsetParent[0]).css('border-left-width') ) || 0

      // Subtract the two offsets
      return {
        top:  offset.top  - parentOffset.top,
        left: offset.left - parentOffset.left
      }
    },
    offsetParent: function() {
      return this.map(function(){
        var parent = this.offsetParent || document.body
        while (parent && !rootNodeRE.test(parent.nodeName) && $(parent).css("position") == "static")
          parent = parent.offsetParent
        return parent
      })
    }
  }

  // for now
  $.fn.detach = $.fn.remove

  // Generate the `width` and `height` functions
  ;['width', 'height'].forEach(function(dimension){
    var dimensionProperty =
      dimension.replace(/./, function(m){ return m[0].toUpperCase() })

    $.fn[dimension] = function(value){
      var offset, el = this[0]
      if (value === undefined) return isWindow(el) ? el['inner' + dimensionProperty] :
        isDocument(el) ? el.documentElement['scroll' + dimensionProperty] :
        (offset = this.offset()) && offset[dimension]
      else return this.each(function(idx){
        el = $(this)
        el.css(dimension, funcArg(this, value, idx, el[dimension]()))
      })
    }
  })

  function traverseNode(node, fun) {
    fun(node)
    for (var i = 0, len = node.childNodes.length; i < len; i++)
      traverseNode(node.childNodes[i], fun)
  }

  // Generate the `after`, `prepend`, `before`, `append`,
  // `insertAfter`, `insertBefore`, `appendTo`, and `prependTo` methods.
  adjacencyOperators.forEach(function(operator, operatorIndex) {
    var inside = operatorIndex % 2 //=> prepend, append

    $.fn[operator] = function(){
      // arguments can be nodes, arrays of nodes, Zepto objects and HTML strings
      var argType, nodes = $.map(arguments, function(arg) {
            var arr = []
            argType = type(arg)
            if (argType == "array") {
              arg.forEach(function(el) {
                if (el.nodeType !== undefined) return arr.push(el)
                else if ($.zepto.isZ(el)) return arr = arr.concat(el.get())
                arr = arr.concat(zepto.fragment(el))
              })
              return arr
            }
            return argType == "object" || arg == null ?
              arg : zepto.fragment(arg)
          }),
          parent, copyByClone = this.length > 1
      if (nodes.length < 1) return this

      return this.each(function(_, target){
        parent = inside ? target : target.parentNode

        // convert all methods to a "before" operation
        target = operatorIndex == 0 ? target.nextSibling :
                 operatorIndex == 1 ? target.firstChild :
                 operatorIndex == 2 ? target :
                 null

        var parentInDocument = $.contains(document.documentElement, parent)

        nodes.forEach(function(node){
          if (copyByClone) node = node.cloneNode(true)
          else if (!parent) return $(node).remove()

          parent.insertBefore(node, target)
          if (parentInDocument) traverseNode(node, function(el){
            if (el.nodeName != null && el.nodeName.toUpperCase() === 'SCRIPT' &&
               (!el.type || el.type === 'text/javascript') && !el.src){
              var target = el.ownerDocument ? el.ownerDocument.defaultView : window
              target['eval'].call(target, el.innerHTML)
            }
          })
        })
      })
    }

    // after    => insertAfter
    // prepend  => prependTo
    // before   => insertBefore
    // append   => appendTo
    $.fn[inside ? operator+'To' : 'insert'+(operatorIndex ? 'Before' : 'After')] = function(html){
      $(html)[operator](this)
      return this
    }
  })

  zepto.Z.prototype = Z.prototype = $.fn

  // Export internal API functions in the `$.zepto` namespace
  zepto.uniq = uniq
  zepto.deserializeValue = deserializeValue
  $.zepto = zepto

  return $
})()

window.Zepto = Zepto
window.$ === undefined && (window.$ = Zepto)

;(function($){
  var _zid = 1, undefined,
      slice = Array.prototype.slice,
      isFunction = $.isFunction,
      isString = function(obj){ return typeof obj == 'string' },
      handlers = {},
      specialEvents={},
      focusinSupported = 'onfocusin' in window,
      focus = { focus: 'focusin', blur: 'focusout' },
      hover = { mouseenter: 'mouseover', mouseleave: 'mouseout' }

  specialEvents.click = specialEvents.mousedown = specialEvents.mouseup = specialEvents.mousemove = 'MouseEvents'

  function zid(element) {
    return element._zid || (element._zid = _zid++)
  }
  function findHandlers(element, event, fn, selector) {
    event = parse(event)
    if (event.ns) var matcher = matcherFor(event.ns)
    return (handlers[zid(element)] || []).filter(function(handler) {
      return handler
        && (!event.e  || handler.e == event.e)
        && (!event.ns || matcher.test(handler.ns))
        && (!fn       || zid(handler.fn) === zid(fn))
        && (!selector || handler.sel == selector)
    })
  }
  function parse(event) {
    var parts = ('' + event).split('.')
    return {e: parts[0], ns: parts.slice(1).sort().join(' ')}
  }
  function matcherFor(ns) {
    return new RegExp('(?:^| )' + ns.replace(' ', ' .* ?') + '(?: |$)')
  }

  function eventCapture(handler, captureSetting) {
    return handler.del &&
      (!focusinSupported && (handler.e in focus)) ||
      !!captureSetting
  }

  function realEvent(type) {
    return hover[type] || (focusinSupported && focus[type]) || type
  }

  function add(element, events, fn, data, selector, delegator, capture){
    var id = zid(element), set = (handlers[id] || (handlers[id] = []))
    events.split(/\s/).forEach(function(event){
      if (event == 'ready') return $(document).ready(fn)
      var handler   = parse(event)
      handler.fn    = fn
      handler.sel   = selector
      // emulate mouseenter, mouseleave
      if (handler.e in hover) fn = function(e){
        var related = e.relatedTarget
        if (!related || (related !== this && !$.contains(this, related)))
          return handler.fn.apply(this, arguments)
      }
      handler.del   = delegator
      var callback  = delegator || fn
      handler.proxy = function(e){
        e = compatible(e)
        if (e.isImmediatePropagationStopped()) return
        e.data = data
        var result = callback.apply(element, e._args == undefined ? [e] : [e].concat(e._args))
        if (result === false) e.preventDefault(), e.stopPropagation()
        return result
      }
      handler.i = set.length
      set.push(handler)
      if ('addEventListener' in element)
        element.addEventListener(realEvent(handler.e), handler.proxy, eventCapture(handler, capture))
    })
  }
  function remove(element, events, fn, selector, capture){
    var id = zid(element)
    ;(events || '').split(/\s/).forEach(function(event){
      findHandlers(element, event, fn, selector).forEach(function(handler){
        delete handlers[id][handler.i]
      if ('removeEventListener' in element)
        element.removeEventListener(realEvent(handler.e), handler.proxy, eventCapture(handler, capture))
      })
    })
  }

  $.event = { add: add, remove: remove }

  $.proxy = function(fn, context) {
    var args = (2 in arguments) && slice.call(arguments, 2)
    if (isFunction(fn)) {
      var proxyFn = function(){ return fn.apply(context, args ? args.concat(slice.call(arguments)) : arguments) }
      proxyFn._zid = zid(fn)
      return proxyFn
    } else if (isString(context)) {
      if (args) {
        args.unshift(fn[context], fn)
        return $.proxy.apply(null, args)
      } else {
        return $.proxy(fn[context], fn)
      }
    } else {
      throw new TypeError("expected function")
    }
  }

  $.fn.bind = function(event, data, callback){
    return this.on(event, data, callback)
  }
  $.fn.unbind = function(event, callback){
    return this.off(event, callback)
  }
  $.fn.one = function(event, selector, data, callback){
    return this.on(event, selector, data, callback, 1)
  }

  var returnTrue = function(){return true},
      returnFalse = function(){return false},
      ignoreProperties = /^([A-Z]|returnValue$|layer[XY]$|webkitMovement[XY]$)/,
      eventMethods = {
        preventDefault: 'isDefaultPrevented',
        stopImmediatePropagation: 'isImmediatePropagationStopped',
        stopPropagation: 'isPropagationStopped'
      }

  function compatible(event, source) {
    if (source || !event.isDefaultPrevented) {
      source || (source = event)

      $.each(eventMethods, function(name, predicate) {
        var sourceMethod = source[name]
        event[name] = function(){
          this[predicate] = returnTrue
          return sourceMethod && sourceMethod.apply(source, arguments)
        }
        event[predicate] = returnFalse
      })

      event.timeStamp || (event.timeStamp = Date.now())

      if (source.defaultPrevented !== undefined ? source.defaultPrevented :
          'returnValue' in source ? source.returnValue === false :
          source.getPreventDefault && source.getPreventDefault())
        event.isDefaultPrevented = returnTrue
    }
    return event
  }

  function createProxy(event) {
    var key, proxy = { originalEvent: event }
    for (key in event)
      if (!ignoreProperties.test(key) && event[key] !== undefined) proxy[key] = event[key]

    return compatible(proxy, event)
  }

  $.fn.delegate = function(selector, event, callback){
    return this.on(event, selector, callback)
  }
  $.fn.undelegate = function(selector, event, callback){
    return this.off(event, selector, callback)
  }

  $.fn.live = function(event, callback){
    $(document.body).delegate(this.selector, event, callback)
    return this
  }
  $.fn.die = function(event, callback){
    $(document.body).undelegate(this.selector, event, callback)
    return this
  }

  $.fn.on = function(event, selector, data, callback, one){
    var autoRemove, delegator, $this = this
    if (event && !isString(event)) {
      $.each(event, function(type, fn){
        $this.on(type, selector, data, fn, one)
      })
      return $this
    }

    if (!isString(selector) && !isFunction(callback) && callback !== false)
      callback = data, data = selector, selector = undefined
    if (callback === undefined || data === false)
      callback = data, data = undefined

    if (callback === false) callback = returnFalse

    return $this.each(function(_, element){
      if (one) autoRemove = function(e){
        remove(element, e.type, callback)
        return callback.apply(this, arguments)
      }

      if (selector) delegator = function(e){
        var evt, match = $(e.target).closest(selector, element).get(0)
        if (match && match !== element) {
          evt = $.extend(createProxy(e), {currentTarget: match, liveFired: element})
          return (autoRemove || callback).apply(match, [evt].concat(slice.call(arguments, 1)))
        }
      }

      add(element, event, callback, data, selector, delegator || autoRemove)
    })
  }
  $.fn.off = function(event, selector, callback){
    var $this = this
    if (event && !isString(event)) {
      $.each(event, function(type, fn){
        $this.off(type, selector, fn)
      })
      return $this
    }

    if (!isString(selector) && !isFunction(callback) && callback !== false)
      callback = selector, selector = undefined

    if (callback === false) callback = returnFalse

    return $this.each(function(){
      remove(this, event, callback, selector)
    })
  }

  $.fn.trigger = function(event, args){
    event = (isString(event) || $.isPlainObject(event)) ? $.Event(event) : compatible(event)
    event._args = args
    return this.each(function(){
      // handle focus(), blur() by calling them directly
      if (event.type in focus && typeof this[event.type] == "function") this[event.type]()
      // items in the collection might not be DOM elements
      else if ('dispatchEvent' in this) this.dispatchEvent(event)
      else $(this).triggerHandler(event, args)
    })
  }

  // triggers event handlers on current element just as if an event occurred,
  // doesn't trigger an actual event, doesn't bubble
  $.fn.triggerHandler = function(event, args){
    var e, result
    this.each(function(i, element){
      e = createProxy(isString(event) ? $.Event(event) : event)
      e._args = args
      e.target = element
      $.each(findHandlers(element, event.type || event), function(i, handler){
        result = handler.proxy(e)
        if (e.isImmediatePropagationStopped()) return false
      })
    })
    return result
  }

  // shortcut methods for `.bind(event, fn)` for each event type
  ;('focusin focusout focus blur load resize scroll unload click dblclick '+
  'mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave '+
  'change select keydown keypress keyup error').split(' ').forEach(function(event) {
    $.fn[event] = function(callback) {
      return (0 in arguments) ?
        this.bind(event, callback) :
        this.trigger(event)
    }
  })

  $.Event = function(type, props) {
    if (!isString(type)) props = type, type = props.type
    var event = document.createEvent(specialEvents[type] || 'Events'), bubbles = true
    if (props) for (var name in props) (name == 'bubbles') ? (bubbles = !!props[name]) : (event[name] = props[name])
    event.initEvent(type, bubbles, true)
    return compatible(event)
  }

})(Zepto)

;(function($){
  var jsonpID = +new Date(),
      document = window.document,
      key,
      name,
      rscript = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
      scriptTypeRE = /^(?:text|application)\/javascript/i,
      xmlTypeRE = /^(?:text|application)\/xml/i,
      jsonType = 'application/json',
      htmlType = 'text/html',
      blankRE = /^\s*$/,
      originAnchor = document.createElement('a')

  originAnchor.href = window.location.href

  // trigger a custom event and return false if it was cancelled
  function triggerAndReturn(context, eventName, data) {
    var event = $.Event(eventName)
    $(context).trigger(event, data)
    return !event.isDefaultPrevented()
  }

  // trigger an Ajax "global" event
  function triggerGlobal(settings, context, eventName, data) {
    if (settings.global) return triggerAndReturn(context || document, eventName, data)
  }

  // Number of active Ajax requests
  $.active = 0

  function ajaxStart(settings) {
    if (settings.global && $.active++ === 0) triggerGlobal(settings, null, 'ajaxStart')
  }
  function ajaxStop(settings) {
    if (settings.global && !(--$.active)) triggerGlobal(settings, null, 'ajaxStop')
  }

  // triggers an extra global event "ajaxBeforeSend" that's like "ajaxSend" but cancelable
  function ajaxBeforeSend(xhr, settings) {
    var context = settings.context
    if (settings.beforeSend.call(context, xhr, settings) === false ||
        triggerGlobal(settings, context, 'ajaxBeforeSend', [xhr, settings]) === false)
      return false

    triggerGlobal(settings, context, 'ajaxSend', [xhr, settings])
  }
  function ajaxSuccess(data, xhr, settings, deferred) {
    var context = settings.context, status = 'success'
    settings.success.call(context, data, status, xhr)
    if (deferred) deferred.resolveWith(context, [data, status, xhr])
    triggerGlobal(settings, context, 'ajaxSuccess', [xhr, settings, data])
    ajaxComplete(status, xhr, settings)
  }
  // type: "timeout", "error", "abort", "parsererror"
  function ajaxError(error, type, xhr, settings, deferred) {
    var context = settings.context
    settings.error.call(context, xhr, type, error)
    if (deferred) deferred.rejectWith(context, [xhr, type, error])
    triggerGlobal(settings, context, 'ajaxError', [xhr, settings, error || type])
    ajaxComplete(type, xhr, settings)
  }
  // status: "success", "notmodified", "error", "timeout", "abort", "parsererror"
  function ajaxComplete(status, xhr, settings) {
    var context = settings.context
    settings.complete.call(context, xhr, status)
    triggerGlobal(settings, context, 'ajaxComplete', [xhr, settings])
    ajaxStop(settings)
  }

  function ajaxDataFilter(data, type, settings) {
    if (settings.dataFilter == empty) return data
    var context = settings.context
    return settings.dataFilter.call(context, data, type)
  }

  // Empty function, used as default callback
  function empty() {}

  $.ajaxJSONP = function(options, deferred){
    if (!('type' in options)) return $.ajax(options)

    var _callbackName = options.jsonpCallback,
      callbackName = ($.isFunction(_callbackName) ?
        _callbackName() : _callbackName) || ('Zepto' + (jsonpID++)),
      script = document.createElement('script'),
      originalCallback = window[callbackName],
      responseData,
      abort = function(errorType) {
        $(script).triggerHandler('error', errorType || 'abort')
      },
      xhr = { abort: abort }, abortTimeout

    if (deferred) deferred.promise(xhr)

    $(script).on('load error', function(e, errorType){
      clearTimeout(abortTimeout)
      $(script).off().remove()

      if (e.type == 'error' || !responseData) {
        ajaxError(null, errorType || 'error', xhr, options, deferred)
      } else {
        ajaxSuccess(responseData[0], xhr, options, deferred)
      }

      window[callbackName] = originalCallback
      if (responseData && $.isFunction(originalCallback))
        originalCallback(responseData[0])

      originalCallback = responseData = undefined
    })

    if (ajaxBeforeSend(xhr, options) === false) {
      abort('abort')
      return xhr
    }

    window[callbackName] = function(){
      responseData = arguments
    }

    script.src = options.url.replace(/\?(.+)=\?/, '?$1=' + callbackName)
    document.head.appendChild(script)

    if (options.timeout > 0) abortTimeout = setTimeout(function(){
      abort('timeout')
    }, options.timeout)

    return xhr
  }

  $.ajaxSettings = {
    // Default type of request
    type: 'GET',
    // Callback that is executed before request
    beforeSend: empty,
    // Callback that is executed if the request succeeds
    success: empty,
    // Callback that is executed the the server drops error
    error: empty,
    // Callback that is executed on request complete (both: error and success)
    complete: empty,
    // The context for the callbacks
    context: null,
    // Whether to trigger "global" Ajax events
    global: true,
    // Transport
    xhr: function () {
      return new window.XMLHttpRequest()
    },
    // MIME types mapping
    // IIS returns Javascript as "application/x-javascript"
    accepts: {
      script: 'text/javascript, application/javascript, application/x-javascript',
      json:   jsonType,
      xml:    'application/xml, text/xml',
      html:   htmlType,
      text:   'text/plain'
    },
    // Whether the request is to another domain
    crossDomain: false,
    // Default timeout
    timeout: 0,
    // Whether data should be serialized to string
    processData: true,
    // Whether the browser should be allowed to cache GET responses
    cache: true,
    //Used to handle the raw response data of XMLHttpRequest.
    //This is a pre-filtering function to sanitize the response.
    //The sanitized response should be returned
    dataFilter: empty
  }

  function mimeToDataType(mime) {
    if (mime) mime = mime.split(';', 2)[0]
    return mime && ( mime == htmlType ? 'html' :
      mime == jsonType ? 'json' :
      scriptTypeRE.test(mime) ? 'script' :
      xmlTypeRE.test(mime) && 'xml' ) || 'text'
  }

  function appendQuery(url, query) {
    if (query == '') return url
    return (url + '&' + query).replace(/[&?]{1,2}/, '?')
  }

  // serialize payload and append it to the URL for GET requests
  function serializeData(options) {
    if (options.processData && options.data && $.type(options.data) != "string")
      options.data = $.param(options.data, options.traditional)
    if (options.data && (!options.type || options.type.toUpperCase() == 'GET' || 'jsonp' == options.dataType))
      options.url = appendQuery(options.url, options.data), options.data = undefined
  }

  $.ajax = function(options){
    var settings = $.extend({}, options || {}),
        deferred = $.Deferred && $.Deferred(),
        urlAnchor, hashIndex
    for (key in $.ajaxSettings) if (settings[key] === undefined) settings[key] = $.ajaxSettings[key]

    ajaxStart(settings)

    if (!settings.crossDomain) {
      urlAnchor = document.createElement('a')
      urlAnchor.href = settings.url
      // cleans up URL for .href (IE only), see https://github.com/madrobby/zepto/pull/1049
      urlAnchor.href = urlAnchor.href
      settings.crossDomain = (originAnchor.protocol + '//' + originAnchor.host) !== (urlAnchor.protocol + '//' + urlAnchor.host)
    }

    if (!settings.url) settings.url = window.location.toString()
    if ((hashIndex = settings.url.indexOf('#')) > -1) settings.url = settings.url.slice(0, hashIndex)
    serializeData(settings)

    var dataType = settings.dataType, hasPlaceholder = /\?.+=\?/.test(settings.url)
    if (hasPlaceholder) dataType = 'jsonp'

    if (settings.cache === false || (
         (!options || options.cache !== true) &&
         ('script' == dataType || 'jsonp' == dataType)
        ))
      settings.url = appendQuery(settings.url, '_=' + Date.now())

    if ('jsonp' == dataType) {
      if (!hasPlaceholder)
        settings.url = appendQuery(settings.url,
          settings.jsonp ? (settings.jsonp + '=?') : settings.jsonp === false ? '' : 'callback=?')
      return $.ajaxJSONP(settings, deferred)
    }

    var mime = settings.accepts[dataType],
        headers = { },
        setHeader = function(name, value) { headers[name.toLowerCase()] = [name, value] },
        protocol = /^([\w-]+:)\/\//.test(settings.url) ? RegExp.$1 : window.location.protocol,
        xhr = settings.xhr(),
        nativeSetHeader = xhr.setRequestHeader,
        abortTimeout

    if (deferred) deferred.promise(xhr)

    if (!settings.crossDomain) setHeader('X-Requested-With', 'XMLHttpRequest')
    setHeader('Accept', mime || '*/*')
    if (mime = settings.mimeType || mime) {
      if (mime.indexOf(',') > -1) mime = mime.split(',', 2)[0]
      xhr.overrideMimeType && xhr.overrideMimeType(mime)
    }
    if (settings.contentType || (settings.contentType !== false && settings.data && settings.type.toUpperCase() != 'GET'))
      setHeader('Content-Type', settings.contentType || 'application/x-www-form-urlencoded')

    if (settings.headers) for (name in settings.headers) setHeader(name, settings.headers[name])
    xhr.setRequestHeader = setHeader

    xhr.onreadystatechange = function(){
      if (xhr.readyState == 4) {
        xhr.onreadystatechange = empty
        clearTimeout(abortTimeout)
        var result, error = false
        if ((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304 || (xhr.status == 0 && protocol == 'file:')) {
          dataType = dataType || mimeToDataType(settings.mimeType || xhr.getResponseHeader('content-type'))

          if (xhr.responseType == 'arraybuffer' || xhr.responseType == 'blob')
            result = xhr.response
          else {
            result = xhr.responseText

            try {
              // http://perfectionkills.com/global-eval-what-are-the-options/
              // sanitize response accordingly if data filter callback provided
              result = ajaxDataFilter(result, dataType, settings)
              if (dataType == 'script')    (1,eval)(result)
              else if (dataType == 'xml')  result = xhr.responseXML
              else if (dataType == 'json') result = blankRE.test(result) ? null : $.parseJSON(result)
            } catch (e) { error = e }

            if (error) return ajaxError(error, 'parsererror', xhr, settings, deferred)
          }

          ajaxSuccess(result, xhr, settings, deferred)
        } else {
          ajaxError(xhr.statusText || null, xhr.status ? 'error' : 'abort', xhr, settings, deferred)
        }
      }
    }

    if (ajaxBeforeSend(xhr, settings) === false) {
      xhr.abort()
      ajaxError(null, 'abort', xhr, settings, deferred)
      return xhr
    }

    var async = 'async' in settings ? settings.async : true
    xhr.open(settings.type, settings.url, async, settings.username, settings.password)

    if (settings.xhrFields) for (name in settings.xhrFields) xhr[name] = settings.xhrFields[name]

    for (name in headers) nativeSetHeader.apply(xhr, headers[name])

    if (settings.timeout > 0) abortTimeout = setTimeout(function(){
        xhr.onreadystatechange = empty
        xhr.abort()
        ajaxError(null, 'timeout', xhr, settings, deferred)
      }, settings.timeout)

    // avoid sending empty string (#319)
    xhr.send(settings.data ? settings.data : null)
    return xhr
  }

  // handle optional data/success arguments
  function parseArguments(url, data, success, dataType) {
    if ($.isFunction(data)) dataType = success, success = data, data = undefined
    if (!$.isFunction(success)) dataType = success, success = undefined
    return {
      url: url
    , data: data
    , success: success
    , dataType: dataType
    }
  }

  $.get = function(/* url, data, success, dataType */){
    return $.ajax(parseArguments.apply(null, arguments))
  }

  $.post = function(/* url, data, success, dataType */){
    var options = parseArguments.apply(null, arguments)
    options.type = 'POST'
    return $.ajax(options)
  }

  $.getJSON = function(/* url, data, success */){
    var options = parseArguments.apply(null, arguments)
    options.dataType = 'json'
    return $.ajax(options)
  }

  $.fn.load = function(url, data, success){
    if (!this.length) return this
    var self = this, parts = url.split(/\s/), selector,
        options = parseArguments(url, data, success),
        callback = options.success
    if (parts.length > 1) options.url = parts[0], selector = parts[1]
    options.success = function(response){
      self.html(selector ?
        $('<div>').html(response.replace(rscript, "")).find(selector)
        : response)
      callback && callback.apply(self, arguments)
    }
    $.ajax(options)
    return this
  }

  var escape = encodeURIComponent

  function serialize(params, obj, traditional, scope){
    var type, array = $.isArray(obj), hash = $.isPlainObject(obj)
    $.each(obj, function(key, value) {
      type = $.type(value)
      if (scope) key = traditional ? scope :
        scope + '[' + (hash || type == 'object' || type == 'array' ? key : '') + ']'
      // handle data in serializeArray() format
      if (!scope && array) params.add(value.name, value.value)
      // recurse into nested objects
      else if (type == "array" || (!traditional && type == "object"))
        serialize(params, value, traditional, key)
      else params.add(key, value)
    })
  }

  $.param = function(obj, traditional){
    var params = []
    params.add = function(key, value) {
      if ($.isFunction(value)) value = value()
      if (value == null) value = ""
      this.push(escape(key) + '=' + escape(value))
    }
    serialize(params, obj, traditional)
    return params.join('&').replace(/%20/g, '+')
  }
})(Zepto)

;(function($){
  $.fn.serializeArray = function() {
    var name, type, result = [],
      add = function(value) {
        if (value.forEach) return value.forEach(add)
        result.push({ name: name, value: value })
      }
    if (this[0]) $.each(this[0].elements, function(_, field){
      type = field.type, name = field.name
      if (name && field.nodeName.toLowerCase() != 'fieldset' &&
        !field.disabled && type != 'submit' && type != 'reset' && type != 'button' && type != 'file' &&
        ((type != 'radio' && type != 'checkbox') || field.checked))
          add($(field).val())
    })
    return result
  }

  $.fn.serialize = function(){
    var result = []
    this.serializeArray().forEach(function(elm){
      result.push(encodeURIComponent(elm.name) + '=' + encodeURIComponent(elm.value))
    })
    return result.join('&')
  }

  $.fn.submit = function(callback) {
    if (0 in arguments) this.bind('submit', callback)
    else if (this.length) {
      var event = $.Event('submit')
      this.eq(0).trigger(event)
      if (!event.isDefaultPrevented()) this.get(0).submit()
    }
    return this
  }

})(Zepto)

;(function(){
  // getComputedStyle shouldn't freak out when called
  // without a valid element as argument
  try {
    getComputedStyle(undefined)
  } catch(e) {
    var nativeGetComputedStyle = getComputedStyle
    window.getComputedStyle = function(element, pseudoElement){
      try {
        return nativeGetComputedStyle(element, pseudoElement)
      } catch(e) {
        return null
      }
    }
  }
})()
  return Zepto
}));





//     Zepto.js
//     (c) 2010-2015 Thomas Fuchs
//     Zepto.js may be freely distributed under the MIT license.

/**
 * 回调函数管理：添加add() 移除remove()、触发fire()、锁定lock()、禁用disable()回调函数。它为Deferred异步队列提供支持
 * 原理：通过一个数组保存回调函数，其他方法围绕此数组进行检测和操作
 *
 *
 *  标记：
 *      once： 回调只能触发一次
 *      memory 记录上一次触发回调函数列表时的参数，之后添加的函数都用这参数立即执行
 *      unique  一个回调函数只能被添加一次
 *      stopOnFalse 当某个回调函数返回false时中断执行
 */
;(function($){
  // Create a collection of callbacks to be fired in a sequence, with configurable behaviour
  // Option flags:
  //   - once: Callbacks fired at most one time.
  //   - memory: Remember the most recent context and arguments
  //   - stopOnFalse: Cease iterating over callback list
  //   - unique: Permit adding at most one instance of the same callback
  $.Callbacks = function(options) {
    options = $.extend({}, options)

    var memory, // Last fire value (for non-forgettable lists)
        fired,  // Flag to know if list was already fired    //是否回调过
        firing, // Flag to know if list is currently firing  //回调函数列表是否正在执行中
        firingStart, // First callback to fire (used internally by add and fireWith) //第一回调函数的下标
        firingLength, // End of the loop when firing   //回调函数列表长度？
        firingIndex, // Index of currently firing callback (modified by remove if needed)
        list = [], // Actual callback list     //回调数据源： 回调列表
        stack = !options.once && [], // Stack of fire calls for repeatable lists//回调只能触发一次的时候，stack永远为false

        /**
         * 回调底层函数
         */
        fire = function(data) {
          memory = options.memory && data   //记忆模式，触发过后，再添加新回调，也立即触发。
          fired = true
          firingIndex = firingStart || 0
          firingStart = 0
          firingLength = list.length
          firing = true      //标记正在回调

            //遍历回调列表
          for ( ; list && firingIndex < firingLength ; ++firingIndex ) {
              //如果 list[ firingIndex ] 为false，且stopOnFalse（中断）模式
              //list[firingIndex].apply(data[0], data[1])  这是执行回调
            if (list[firingIndex].apply(data[0], data[1]) === false && options.stopOnFalse) {
              memory = false  //中断回调执行
              break
            }
          }
          firing = false //回调执行完毕
          if (list) {
              //stack里还缓存有未执行的回调
            if (stack) stack.length && fire(stack.shift())  //执行stack里的回调
            else if (memory) list.length = 0 //memory 清空回调列表    list.length = 0清空数组的技巧
            else Callbacks.disable()             //其他情况如  once 禁用回调
          }
        },

        Callbacks = {
          //添加一个或一组到回调列表里
          add: function() {
            if (list) {        //回调列表已存在
              var start = list.length,   //位置从最后一个开始
                  add = function(args) {
                    $.each(args, function(_, arg){
                      if (typeof arg === "function") {    //是函数
                          //非unique，或者是unique，但回调列表未添加过
                        if (!options.unique || !Callbacks.has(arg)) list.push(arg)
                      }
                      //是数组/伪数组，添加，重新遍历
                      else if (arg && arg.length && typeof arg !== 'string') add(arg)
                    })
                  }

                //添加进列表
              add(arguments)

                //如果列表正在执行中，修正长度，使得新添加的回调也可以执行
              if (firing) firingLength = list.length
              else if (memory) {
                  //memory 模式下，修正开始下标，
                firingStart = start
                fire(memory)         //立即执行所有回调
              }
            }
            return this
          },

            //从回调列表里删除一个或一组回调函数
          remove: function() {
            if (list) {       //回调列表存在才可以删除
                //_作废参数
                //遍历参数
              $.each(arguments, function(_, arg){
                var index

                  //如果arg在回调列表里
                while ((index = $.inArray(arg, list, index)) > -1) {
                  list.splice(index, 1)                                //执行删除
                  // Handle firing indexes
                    //回调正在执行中
                  if (firing) {
                      //避免回调列表溢出
                    if (index <= firingLength) --firingLength  //在正执行的回调函数后，递减结尾下标
                    if (index <= firingIndex) --firingIndex     //在正执行的回调函数前，递减开始下标
                  }
                }
              })
            }
            return this
          },

            /**
             * 检查指定的回调函数是否在回调列表中
             * @param fn
             * @returns {boolean}
             */
          has: function(fn) {
              //
            return !!(list && (fn ? $.inArray(fn, list) > -1 : list.length))
          },

            /**
             * 清空回调函数
             * @returns {*}
             */
          empty: function() {
            firingLength = list.length = 0
            return this
          },

            //禁用回调函数
          disable: function() {
            list = stack = memory = undefined
            return this
          },

            /**
             * 是否已禁用回调函数
             * @returns {boolean}
             */
          disabled: function() {
            return !list
          },
            /**
             * 锁定回调函数
             * @returns {*}
             */
          lock: function() {
            stack = undefined;   //导致无法触发

             //非memory模式下，禁用列表
            if (!memory) Callbacks.disable()
            return this
          },
            /**
             * 是否是锁定的
             * @returns {boolean}
             */
          locked: function() {
            return !stack
          },

            /**
             * 用上下文、参数执行列表中的所有回调函数
             * @param context
             * @param args
             * @returns {*}
             */
          fireWith: function(context, args) {
                // 未回调过，非锁定、禁用时
            if (list && (!fired || stack)) {

              args = args || []
              args = [context, args.slice ? args.slice() : args]
              if (firing) stack.push(args)  //正在回调中  ，存入static

              else fire(args) //否则立即回调
            }
            return this
          },

            /**
             * 用参数执行列表中的所有回调函数
             * @param context
             * @param args
             * @returns {*}
             */
          fire: function() {
                //执行回调
            return Callbacks.fireWith(this, arguments)
          },

            /**
             * 回调列表是否被回调过
             * @returns {boolean}
             */
          fired: function() {
            return !!fired
          }
        }

    return Callbacks
  }
})(Zepto);






//     Zepto.js
//     (c) 2010-2015 Thomas Fuchs
//     Zepto.js may be freely distributed under the MIT license.
//
//     Some code (c) 2005, 2013 jQuery Foundation, Inc. and other contributors

;(function($){
  var slice = Array.prototype.slice

  function Deferred(func) {

    //元组：描述状态、状态切换方法名、对应状态执行方法名、回调列表的关系
      //tuple引自C++/python，和list的区别是，它不可改变 ，用来存储常量集
    var tuples = [
          // action, add listener, listener list, final state
          [ "resolve", "done", $.Callbacks({once:1, memory:1}), "resolved" ],
          [ "reject", "fail", $.Callbacks({once:1, memory:1}), "rejected" ],
          [ "notify", "progress", $.Callbacks({memory:1}) ]
        ],
        state = "pending", //Promise初始状态

        //promise对象，promise和deferred的区别是:
        /*promise只包含执行阶段的方法always(),then(),done(),fail(),progress()及辅助方法state()、promise()等。
          deferred则在继承promise的基础上，增加切换状态的方法，resolve()/resolveWith(),reject()/rejectWith(),notify()/notifyWith()*/
        //所以称promise是deferred的只读副本
        promise = {
            /**
             * 返回状态
             * @returns {string}
             */
          state: function() {
            return state
          },
            /**
             * 成功/失败状态的 回调调用
             * @returns {*}
             */
          always: function() {
            deferred.done(arguments).fail(arguments)
            return this
          },
            /**
             *
             * @returns promise对象
             */
          then: function(/* fnDone [, fnFailed [, fnProgress]] */) {
            var fns = arguments

            //注意，这无论如何都会返回一个新的Deferred只读副本，
            //所以正常为一个deferred添加成功，失败，千万不要用then，用done，fail
            return Deferred(function(defer){
              $.each(tuples, function(i, tuple){
                //i==0: done   i==1: fail  i==2 progress
                var fn = $.isFunction(fns[i]) && fns[i]

                //执行新deferred done/fail/progress
                deferred[tuple[1]](function(){
                    //直接执行新添加的回调 fnDone fnFailed fnProgress
                  var returned = fn && fn.apply(this, arguments)

                    //返回结果是promise对象
                  if (returned && $.isFunction(returned.promise)) {
                     //转向fnDone fnFailed fnProgress返回的promise对象
                     //注意，这里是两个promise对象的数据交流
                      //新deferrred对象切换为对应的成功/失败/通知状态，传递的参数为 returned.promise() 给予的参数值
                    returned.promise()
                      .done(defer.resolve)
                      .fail(defer.reject)
                      .progress(defer.notify)
                  } else {
                    var context = this === promise ? defer.promise() : this,
                        values = fn ? [returned] : arguments
                    defer[tuple[0] + "With"](context, values)//新deferrred对象切换为对应的成功/失败/通知状态
                  }
                })
              })
              fns = null
            }).promise()
          },

            /**
             * 返回obj的promise对象
             * @param obj
             * @returns {*}
             */
          promise: function(obj) {
            return obj != null ? $.extend( obj, promise ) : promise
          }
        },

        //内部封装deferred对象
        deferred = {}

    //给deferred添加切换状态方法
    $.each(tuples, function(i, tuple){
      var list = tuple[2],//$.Callback
          stateString = tuple[3]//   状态 如 resolved

        //扩展promise的done、fail、progress为Callback的add方法，使其成为回调列表
        //简单写法：  promise['done'] = jQuery.Callbacks( "once memory" ).add
        //         promise['fail'] = jQuery.Callbacks( "once memory" ).add  promise['progress'] = jQuery.Callbacks( "memory" ).add
      promise[tuple[1]] = list.add

        //切换的状态是resolve成功/reject失败
        //添加首组方法做预处理，修改state的值，使成功或失败互斥，锁定progress回调列表，
      if (stateString) {
        list.add(function(){
          state = stateString

            //i^1  ^异或运算符  0^1=1 1^1=0，成功或失败回调互斥，调用一方，禁用另一方
        }, tuples[i^1][2].disable, tuples[2][2].lock)
      }

        //添加切换状态方法 resolve()/resolveWith(),reject()/rejectWith(),notify()/notifyWith()
      deferred[tuple[0]] = function(){
        deferred[tuple[0] + "With"](this === deferred ? promise : this, arguments)
        return this
      }
      deferred[tuple[0] + "With"] = list.fireWith
    })

    //deferred继承promise的执行方法
    promise.promise(deferred)

    //传递了参数func，执行
    if (func) func.call(deferred, deferred)

    //返回deferred对象
    return deferred
  }

    /**
     *
     * 主要用于多异步队列处理。
       多异步队列都成功，执行成功方法，一个失败，执行失败方法
       也可以传非异步队列对象

     * @param sub
     * @returns {*}
     */
  $.when = function(sub) {
    var resolveValues = slice.call(arguments), //队列数组 ，未传参数是[]
        len = resolveValues.length,//队列个数
        i = 0,
        remain = len !== 1 || (sub && $.isFunction(sub.promise)) ? len : 0, //子def计数
        deferred = remain === 1 ? sub : Deferred(),//主def,如果是1个fn，直接以它为主def，否则建立新的Def
        progressValues, progressContexts, resolveContexts,
        updateFn = function(i, ctx, val){
          return function(value){
            ctx[i] = this    //this
            val[i] = arguments.length > 1 ? slice.call(arguments) : value   // val 调用成功函数列表的参数
            if (val === progressValues) {
              deferred.notifyWith(ctx, val)  // 如果是通知，调用主函数的通知，通知可以调用多次
            } else if (!(--remain)) {          //如果是成功，则需等成功计数为0，即所有子def都成功执行了，remain变为0，
              deferred.resolveWith(ctx, val)      //调用主函数的成功
            }
          }
        }

      //长度大于1，
    if (len > 1) {
      progressValues = new Array(len) //
      progressContexts = new Array(len)
      resolveContexts = new Array(len)

      //遍历每个对象
      for ( ; i < len; ++i ) {
         //如果是def，
        if (resolveValues[i] && $.isFunction(resolveValues[i].promise)) {
          resolveValues[i].promise()
            .done(updateFn(i, resolveContexts, resolveValues)) //每一个成功
            .fail(deferred.reject)//直接挂入主def的失败通知函数,当某个子def失败时，调用主def的切换失败状态方法，执行主def的失败函数列表
            .progress(updateFn(i, progressContexts, progressValues))
        } else {
          --remain   //非def，直接标记成功，减1
        }
      }
    }

    //都为非def，比如无参数，或者所有子队列全为非def，直接通知成功，进入成功函数列表
    if (!remain) deferred.resolveWith(resolveContexts, resolveValues)
    return deferred.promise()
  }

  $.Deferred = Deferred
})(Zepto);

;/*!js/utils.js*/
/**
 * @global
 * iOS设备对照表
 */
var DEVICEMAP = {
	"iPad1,1": "iPad",
	"iPad2,1": "iPad 2",
	"iPad2,2": "iPad 2",
	"iPad2,3": "iPad 2",
	"iPad2,4": "iPad 2",
	"iPad3,1": "iPad 3",
	"iPad3,2": "iPad 3",
	"iPad3,3": "iPad 3",
	"iPad3,4": "iPad 4",
	"iPad3,5": "iPad 4",
	"iPad3,6": "iPad 4",

	"iPad4,1": "iPad Air",
	"iPad4,2": "iPad Air",
	"iPad4,3": "iPad Air",

	"iPad5,3": "iPad Air 2",
	"iPad5,4": "iPad Air 2",

	"iPad6,7": "iPad Pro",
	"iPad6,8": "iPad Pro",
	"iPad6,3": "iPad Pro",
	"iPad6,4": "iPad Pro",

	"iPad2,5": "iPad mini",
	"iPad2,6": "iPad mini",
	"iPad2,7": "iPad mini",

	"iPad4,4": "iPad mini 2",
	"iPad4,5": "iPad mini 2",
	"iPad4,6": "iPad mini 2",

	"iPad4,7": "iPad mini 3",
	"iPad4,8": "iPad mini 3",
	"iPad4,9": "iPad mini 3",

	"iPad5,1": "iPad mini 4",
	"iPad5,2": "iPad mini 4",

	"iPhone3,1": "iPhone 4",
	"iPhone3,2": "iPhone 4",
	"iPhone3,3": "iPhone 4",
	"iPhone4,1": "iPhone 4S",

	"iPhone5,1": "iPhone 5",
	"iPhone5,2": "iPhone 5",

	"iPhone5,3": "iPhone 5c",
	"iPhone5,4": "iPhone 5c",

	"iPhone6,1": "iPhone 5s",
	"iPhone6,2": "iPhone 5s",

	"iPhone7,2": "iPhone 6",
	"iPhone7,1": "iPhone 6 Plus",

	"iPhone8,1": "iPhone 6s",
	"iPhone8,2": "iPhone 6s Plus",
	"iPhone8,4": "iPhone SE",

	"iPhone9,1": "iPhone 7",
	"iPhone9,3": "iPhone 7",

	"iPhone9,2": "iPhone 7 Plus",
	"iPhone9,4": "iPhone 7 Plus",

	"iPod4,1" : "iPod touch 4G",
	"iPod5,1" : "iPod touch 5G",
	"iPod7,1" : "iPod touch 6G",
	"iPod3,1" : "iPod touch 3G"
};

var _locationName = "http://app.guopan.cn/front/";
window.API = {
	mobileconf : _locationName + "mobileconf.php",  // 获取描述文件
	bindCard : _locationName + "bindCard.php", // 查询是否已经购买  code = 0 成功 msg(0,1,2,...)  msg就代表有多少张卡
	plist : _locationName + "plist.php",  // 安装 vip APP
	plist2 : _locationName + "plist2.php"  // 安装普通APP
}

var ua = navigator.userAgent;

window.utils = {
	_div : $("<span></span"),
	on : function(type, callback){
		this._div.on(type, function(){
			callback && callback.apply(this, Array.prototype.slice.call(arguments, 1));
		})
	},
	fire : function(type){
		this._div.trigger(type, Array.prototype.slice.call(arguments, 1));
	},
	ajax : function(url, method, param, isAsync){
		var def = $.Deferred();
		$.ajax({
			url : url,
			dataType : "json",
			type : method,
			data : param,
			async : isAsync,
			success : function(res){
				def.resolve(res);
			},
			fail : function(res){
				def.reject(res);
			}
		});
		return def;
	},
	get : function(url, param, isAsync){
		return this.ajax(url, "GET", param || {}, isAsync === false ? false : true);
	},
	post : function(url, param, isAsync){
		return this.ajax(url, "POST", param || {}, isAsync === false ? false : true);
	},
    cookie : {
        get : function(key){
            var rep = new RegExp(key + '=([^;]*)?', 'i');
            if (rep.test(document.cookie)) {
                return decodeURIComponent(RegExp.$1);
            } else {
                return null;
            }
        },
        set : function(key, value, hours){
            hours = hours || 24;
            try {
                var exp = new Date();
                exp.setTime(exp.getTime() + hours * 60 * 60 * 1000);
                document.cookie = key + '=' + encodeURIComponent(value) + ';expires=' + exp.toUTCString() + ';path=/';
                return true;
            } catch (e) {
                return false;
            }
        },
        del : function(key){
            var exp = new Date(0);
            document.cookie = key + '=;expires=' + exp.toUTCString() + ';path=/';
        }
    },
    // 获取浏览器URL参数
    getSearchParam : (function(){
        var data = {};
        var _url = location.search;
        _url.replace(/([^=?]*)=([^&]*)&?/ig,function(str, key, value){
            return data[key] = value;
        })
        return data;
    })(),
    isSafari : function(){
		if( /safari/ig.test(ua) && !/(crios|chrome|fxios|qqbrowser|sogou|baidu|ucbrowser|qhbrowser|opera|micromessenger|weibo)/ig.test(ua) ){
			return true;
		}
		return false;
    },
    isWeixin : function(){
        return /micromessenger/ig.test(ua);
    },
	// 获取iOS系统版本号 如 9.3
	getVersion : function(){
		var iosVersion = /.+(?!iphone|ipad|ipod) .+ os ([\d_\.]+).+/gi.exec(ua);
		iosVersion = iosVersion && iosVersion[1] ? iosVersion[1].replace(/_/g, '.') : null;
		return iosVersion;
	},
	// 获取设备类型， iPhone是1, iPad是2, iPod是3
	getDeviceType : function(){
        if(/iphone/gi.test(ua))	return 1;
        if(/ipad/gi.test(ua))	return 2;
        if(/ipod/gi.test(ua))	return 3;
        return 0;	// 对于iOS来说，这种情况不应该存在
	},
	getId : function(id){
		return document.querySelector(id);
	},
	divMask : function(){
		return document.querySelector(".pop-mask");
	},
	createDiv : function(html){
		if(this.divMask()){
			this.closeDiv();
		}
		var div = document.createElement("div");
		div.className = "pop-mask";
		div.innerHTML = '<div class="pop">\
			<div class="close"></div>\
			<div class="pop-inner"></div>\
		</div>';

		document.body.appendChild(div);

		var pop = this.getId(".pop-mask"),
			inner = this.getId(".pop-inner"),
			close = this.getId(".close");

		inner.innerHTML = html;
		pop.getClientRects();
		pop.classList.add("isShow");

		close.addEventListener("click", function(){
			this.closeDiv();
		}.bind(this), false);

		return pop;
	},
	closeDiv : function(){
		var m = this.getId(".pop-mask");
		if(!m.classList) return false;
		m.classList.remove("isShow");
		// m.addEventListener("webkitTransitionEnd", remove, false);
		//m.addEventListener("webkitTransitionEnd", remove, false);
		m.parentNode && m.parentNode.removeChild(m);
	},
	toast : function(msg, param){
		var self = this;
	    param = param || {};
	    var $toast = $('<div class="toast ' + (param.className || '') + '">' + msg + '</div>').appendTo(document.body);
	    var _top  = -$toast[0].offsetHeight / 2,
	        _left = -$toast[0].offsetWidth / 2;
	    $toast.css('margin', _top+'px 0 0 '+_left+'px');
	    if(!$toast.hasClass('modal-in')){
	        $toast[0].getClientRects();
	        $toast.addClass('modal-in');
	    }
	    //上否一直显示在屏幕上
	    if(!param.isShow){
	        setTimeout(function() {
	            $toast.removeClass('modal-in').addClass('modal-out');
	            $toast.one('webkitTransitionEnd transitionend',function(){
	                $(this).remove();
	                param.callback && param.callback.call(self);
	            })
	        }, param.time || 2000);
	    }
	},
	loading : function(){
		var _load = utils.getId(".loading");
		_load && _load.parentNode && _load.parentNode.removeChild(_load);

		var html = '<div class="loading"><div class="loadingMask"></div><div class="loading-modal"><span class="preloader"></span></div></div>';
		document.body.insertAdjacentHTML("beforeEnd", html);
	},
	loadHide : function(){
		var _load = utils.getId(".loading");
		if(!_load) return false;
		_load.parentNode && _load.parentNode.removeChild(_load);
	},
	tongji : function(param){
		var sharUrl = "http://dr.gpweb.guopan.cn/images/share.gif";
		var data = param || {};
		this.get(sharUrl, data);
	}
}

;/*!js/slider.js*/
/*****   guopan yangbys   ********/
// slider
;(function(){

    //
    $.fn.slider = function(param){
        var defualt = {
            width : 280,
            height : 300,
            index : 0
        }
        var option = $.extend({}, defualt, param || {});
        return new mySlider(this, option);
    }

    function mySlider(){
        this.init.apply(this, arguments);
    }

    mySlider.prototype = {
        init : function(obj, opt){

            this.root = obj;
            this.slider = obj.find("ul");
            this.item = this.slider.children("li");
            this.len = this.item.length;
            this.index = opt.index;

            this.options = opt;

            this.len && this.setDotHTML();
            this.loadImg(opt.index);
            this.setStyle();
            this.bindUI();
        },
        bindUI : function(){
            var slider = this.slider[0];
            slider.addEventListener("touchstart", this, false);
            slider.addEventListener("touchmove", this, false);
            slider.addEventListener("touchend", this, false);

            window.addEventListener("resize", this, false);

        },
        handleEvent : function(e){
            var type = e.type;
            switch (type) {
                case "touchstart":
                    this.touchEvent().touchStart(e);
                    break;
                case "touchmove":
                    this.touchEvent().touchMove(e);
                    break;
                case "touchend":
                    this.touchEvent().touchEnd(e);
                    break;
                case "resize":
                    this.resize();
                    break;
            }
        },
        touchEvent : function(){

            var self = this;
            function touch(e){
                return {
                    x : e.targetTouches[0].pageX,
                    y : e.targetTouches[0].pageY
                }
            }

            function start(e){
                if(self.isTouch) return false;
                self.isTouch = true;
                self.offsetX = touch(e).x;
                self.offsetY = touch(e).y;
                self.posX = 0;
                self.startTime = new Date / 1;
                self.transformX = self.getTranform();
                self.slider.css({
                    '-webkit-transition' : 'none',
                            'transition' : 'none'
                })
                e.preventDefault();
            };

            function move(e){
                var x = self.posX = touch(e).x - self.offsetX;
                var y = touch(e).y;
                //&& Math.abs(y) < Math.abs(self.offsetY)
                if(self.isTouch ){
                    var xx = x + self.transformX;
                    if(self.index == 0 || self.index == self.len - 1){
                        xx = self.transformX + x * 0.3;
                    }
                    self.slider.css({
                        '-webkit-transform' : 'translate3d('+ (xx) +'px, 0, 0)',
                        'transform' : 'translate3d('+ (xx) +'px, 0, 0)'
                    })
                    e.preventDefault();
                }
            }

            function end(e){
                var endTime = new Date / 1;
                var pentWidth = self.getWin().w / 6;
                var index = self.index;
                if(endTime - self.startTime > 50){
                    index = Math.abs(self.posX) >= pentWidth ? self.posX > 0 ? (index==0 ? 0 : --index) : (index==self.len-1 ? self.len-1 : ++index) : index;
                    self.index = index;
                }
                self.goPage(index);
                e.preventDefault();
                self.isTouch = false;
                console.log('end')
            }

            return {
                touchStart : start,
                touchMove : move,
                touchEnd : end
            }
        },
        setDotHTML : function(){
            var html = "<div class='dot'>";
            for(var i=0,len=this.len; i<len; i++){
                html += "<span></span>";
            }
            html += "</div>";
            this.root.after(html);
            this.dot = $(".dot");
            this.dot.find("span").first().addClass("cur");
        },
        setDot : function(n){
            return this.dot.find("span").removeClass("cur").eq(n).addClass("cur");
        },
        setStyle : function(){
            var getWin = this.getWin();
            var _width = getWin.w,
                _height = getWin.h;

            this.root.css({width : _width});
            $.each(this.item, function(i, item){
                item.style.cssText = "width:"+_width+"px; left:"+(i*_width)+"px";
            });

            $(".installTip-main").css({
                width : _width,
                height :_height
            });

        },
        goPage : function(n){
            var w = n * this.getWin().w;
            this.setTransform(-w);
            this.setDot(n);
            this.loadImg(n);
        },
        getTranform : function(){
            var slider = this.slider[0];
            var _style = window.getComputedStyle(slider)["webkitTransform"];
            var translateX = _style.split(",").splice(-2,1)[0];
            return parseInt(translateX) || 0;
        },
        setTransform : function(x){
            var slider = this.slider;
            slider.css({
                '-webkit-transition' : 'all .5s',
                        'transition' : 'all .5s',
                 '-webkit-transform' : 'translate3d('+ (x) +'px, 0, 0)',
                         'transform' : 'translate3d('+ (x) +'px, 0, 0)'
            })
        },
        loadImg : function(n){
            var _img = this.item.eq(n).find("img");
            var _src = _img.data("src");
            if(!_src) return false;
            _img.attr("src", _src).removeAttr("data-src");
        },
        getWin : function(){
            var w = Math.max(320, Math.min(768, document.documentElement.clientWidth || document.body.clientWidth)),
                h = document.documentElement.clientHeight || document.body.clientHeight;
            return {
                w : w * 0.8,
                h : h * 0.8
            }
        },
        resize : function(){
            this.getWin();
            this.setStyle();
            this.goPage(this.index);
        }
    }

})();

;/*!js/gpios.js*/


var UDID = "udid";  // cookie UDID
var channelid = "gp_storage_channelid"; // cookie channelid
var POLLING_INTERVAL = 15000;            // 打包请求时间
var saveYearTime = 24 * 365;
var packTimeout = null;

// 获取cookie上的UDID
function getUdid(){
    return utils.cookie.get(UDID) || "";
}

// 添加cookie上的UDID
function setUdid(udid){
    if(udid.length !== 40) return false;
    return utils.cookie.set(UDID, udid, 3 * 24);
}

// 设置是否弹出层显示
function setClass(obj, isremove){
    return isremove ? obj.removeClass("isShow") : obj.addClass("isShow");
}


// guopanIos object
var guopanIos = {
    //初始化
    packTimeout : null,
    init : function(){

        var $vipInstall = $(".vip-install"),
            $oneBtn = $vipInstall.find(".one-install"),
            $towBtn = $vipInstall.find(".two-install"),
            hideClassName = "isHide";

        // 设置从客户端APP过来的参数(如果有就保存到cookie上)
        this.shareParam().init();

        var self = this;

        // 1、判断是否cookie上有udid
        // 2、如果有udid 就直接跳过安装描述文件
        // 3、去充值 或 安装下载APP安装 (前提是该设备有卡)

        // 事件绑定
        this.bindUI();

        // 安装描述文件回来 有一些参数
        var getSearchParam = utils.getSearchParam;
        // param参数
        var primarylink    = getSearchParam.primarylink,
            sid            = getSearchParam.sid,
            udid           = getSearchParam.gp_storage_perpetual_uid,
            deviceName     = getSearchParam.product;

        // 保存渠道channelid ID
         getSearchParam.channelid && utils.cookie.set(channelid , getSearchParam.channelid, saveYearTime);

         // 提供给运营推广用的
         // 是否自动安装 ==> (普通版)
         var _hashs = location.hash;
         if(getSearchParam.autoinstall && _hashs && _hashs.slice(1) == 1){
             if(!utils.isSafari() || /baiduboxapp/i.test(navigator.userAgent)){
                self.showSafariBox();
                 return false;
             }
            setTimeout(function(){
                this.normalInstallAPP();
            }.bind(this), 100)
         }

        if(primarylink && sid && udid){
            utils.cookie.set("NEW_STORAGE_STEP_TWO", 1, 24);
            // 把udid  && device_name 存到cookie上
            setUdid(udid);
            utils.cookie.set("device_name", DEVICEMAP[deviceName], saveYearTime);
            // 把URL上的参数去掉
            history.pushState({}, "", location.origin + location.pathname);
        }

        // 是否已经安装描述 或 cookie有 UDID && device_name
        if(utils.cookie.get("NEW_STORAGE_STEP_TWO") || (getUdid() && utils.cookie.get("device_name")) ){
            utils.getId(".one-tip").classList.add("isHide");
            utils.getId(".two-tip").classList.remove("isHide");
            showBtn();
        }else{
            this.isShow($oneBtn, hideClassName, true);
            // 去拿安装描述文件的url回来
//            utils.get(API.mobileconf).done(function(res){
//                if(res.code == 0){
//                    // 至尊版的描述文件
//                    var $goInstall = $(".goInstall");
//
//                    // 如果不是safari 与 在百度APP应用下 则弹出复制功能窗口
//                    if(!utils.isSafari() || /baiduboxapp/i.test(navigator.userAgent)){
//                        $goInstall.on("click", function(){
//                            self.showSafariBox();
//                            return false
//                        });
//                        return false;
//                    }
//                    $goInstall.attr("href", res.msg);
//                }
//            });
        }

        function showBtn(){
            utils.getId(".i-mobile").innerHTML = utils.cookie.get("device_name");
            utils.getId(".i-info").innerHTML = getUdid().toString().slice(0,20);

            // 该设备是否具备安装的条件
            if(self.isInstall()){

                self.isShow($towBtn, hideClassName, true);
                var _btn = $towBtn.find(".btn");
                _btn.addClass("go-install-btn").html("立即安装");

                // 打包安装APP
                utils.fire("downPack");

            }else{
                self.isShow($oneBtn, hideClassName, false);
                self.isShow($towBtn, hideClassName, true);
                // 支付流程
                utils.fire("pay");
            }
        };

    },

    // 绑定事件
    bindUI : function(){

        // tab切换
        var $tab = $(".tabNav"),
            $tabItem = $tab.find("span"),
            $tabContent = $(".wrap").find(".boxItem");
        var _act = "active";
        var self = this;

        $tab.on("click", "span", function(){
            var _this = $(this),
                _index = _this.index();
            if(_this.hasClass(_act)) return false;
            location.hash = _index;
            changeTab(_index);

            // 设置图文内容
            self.setTabStep(_index);
        });

        var _hash = location.hash;
        if(_hash && _hash.slice(1)){
            var _n = _hash.slice(1);
            _n < $tabItem.length && changeTab(_n);
        }

        function changeTab(n){
            $tabItem.removeClass(_act).eq(n).addClass(_act);
            $tabContent.removeClass(_act).eq(n).addClass(_act);
        }

        // 如果在微信下，则弹出提示框 不执行下面的逻辑
        if(utils.isWeixin()){
            utils.getId(".one-install").classList.remove("isHide");
            $(".install-btn, .normal-install-btn").on("click", function(){
                guopanIos.weixin();
            });
            return false;
        }

        // 如果需要安装描述文件 则需要滑动图
        if(!getUdid()) $(".slider").slider();

        // 滑动图 弹出层
        var $installTip = $(".installTip");

        $(".install-btn").on("click", self.ios(function(){
            setClass($installTip, false);
        }));

        $(".installMask").on("click", function(){
            setClass($installTip, true);
        });


        // 打包至尊版vip APP
        utils.on("downPack", function(){
            $(".go-install-btn.btn").off("click").on("click", self.ios(function(){

                  self.vipInstallApp();
                //   setTimeout(function(){
                //       location.href = "XXAppstore://";
                //   },50)
            }));
        })

        // 打包普通版 APP
        $(".normal-install-btn.btn").off("click").on("click", self.ios(function(){
            // 如果不是safari 与 在百度APP应用下 则弹出复制功能窗口
            if(!utils.isSafari() || /baiduboxapp/i.test(navigator.userAgent)){
                self.showSafariBox();
                return false;
            }
            // self.Progress().showProgressBox();
            self.normalInstallAPP();
            // setTimeout(function(){
            //     location.href = "XXAppstore://";
            // },50)
        }));

        // 支付
        utils.on("pay", function(){
            $(".pay-btn.btn").off("click").on("click", function(){
                var _html = self.payHTML();
                var payPop =  utils.createDiv(_html);
                var $pay = $(payPop);
                var type = 1;

                var $payWays = $pay.find(".pay-ways"),
                    $payItem = $payWays.find(".pay-way");

                // 支付方式切换
                $pay.on("click", ".pay-way", function(){
                    var _this = $(this),
                        _index = _this.index();
                    var _s = "selected";
                    if(_this.hasClass(_s)) return false;
                    type = _this.data("type");
                    $payItem.removeClass(_s).eq(_index).addClass(_s);
                // 支付
                }).on("click", "#pay", function(){
                    var data = {
                        buy : "voucher",
                        type : type,
                        device_name : utils.cookie.get("device_name")
                    }
                    utils.loading();
                    // 微信支付弹出提示
                    if(type == 2){
                        var wxTipHtml = '<div class="wxTips">\
                            <h6>请选择您的支付状态进行下一步操作</h6>\
                            <div class="wxBtm row">\
                                <a class="wxBtns col" href="javascript:location.reload();">支付遇到问题</a>\
                                <a class="wxBtns col" href="javascript:location.reload();">支付完成</a>\
                            </div>\
                        </div>';
                        utils.createDiv(wxTipHtml);
                        $(".pop-mask").find(".close").hide();
                    }

                    utils.post(API.bindCard, data).done(function(res){
                        // code==0 则成功去支付页面
                        if(res.code == 0){
                            location.href = res.msg;
                        }else{
                            return utils.toast(res.msg);
                        }
                    }).always(function(){
                        utils.loadHide();
                    });
                })

            });
        })


        //  cydia安装
        $(".cydia-install-btn.btn").on("click", function(){
            location.href ="cydia://url/https://cydia.saurik.com/api/share#?source=http://apt.xxzhushou.cn/&package=com.xxfl.flstore.jb";
        });

        // 埋点
        $("[data-eventkey]").on("click", function(){
            var _key = $(this).data("eventkey");
            if(self.shareParam().get()){
                var data = $.extend(true, self.shareParam().get(), {eventkey : _key});
                utils.tongji(data);
            }
        })

    },

    // 用户支付HTML
    payHTML : function(){
        var payHTML = '<div class="pay-header">\
                        <div class="goods-name">商品名称：十元代金券</div>\
                        <div class="goods-price">支付金额: <em>10.00元</em></div></div>\
                        <div class="pay-body">\
                            <h2>选择支付方式</h2>\
                            <ul class="pay-ways row">\
                                <li class="pay-way col selected" data-type="1">\
                                    <img src="img/icon-alipay.png">\
                                    <span>支付宝</span>\
                                </li>\
                                <li class="pay-way col" data-type="2">\
                                    <img src="img/icon-wx.png">\
                                    <span>微信支付</span>\
                                </li>\
                                <li class="pay-way col" data-type="3">\
                                    <img src="img/icon-bankcard.png">\
                                    <span>银行卡</span>\
                                </li>\
                            </ul>\
                        </div>\
                    </div>\
                    <div class="pop-footer"><a href="javascript:void(0)" class="btn" id="pay">确认支付</a></div>';


        return payHTML;
    },

    // 是否有安装APP的权限
    isInstall : function(){
        var isFlat = false;
        utils.loading();
        utils.get(API.bindCard, {get: "checkbindcard"}, false).done(function(res){
            if(res.code == 0){
                if(res.msg > 0) isFlat = true;
            }
        }).always(function(){
            utils.loadHide();
        })
        return isFlat;
    },

    // 至尊版打包下载
    vipInstallApp : function(){
        // 抛出下载进度条
        this.Progress().showProgressBox();
        // utils.loading();
        var installData = {
            ios_version : utils.getVersion(),
            device_type : utils.getDeviceType(),
            channelid : this.getChannelid()
        }
        var self = this;
        clearTimeout(packTimeout),packTimeout = null;
        //打包过程
        function installApp(){
            utils.get(API.plist, installData, false).done(function(res){
                if(res.code == 0){
                    if(res.msg.data && res.msg.data.status != 0){
                        // 不停的打包
                        packTimeout = setTimeout(installApp, POLLING_INTERVAL);
                    }else{
                        //打包成功
                        if(!utils.getId(".installSuccess")){
                            $(".vip-install").append("<p class='installSuccess'>安装完成后，请前往桌面查看哦</p>");
                        }
                        self.Progress().hideProgressBox();
                        window.location.href = res.msg.data.aplist;
                    }
                }else{
                    self.Progress().hideProgressBox();
                    return alert(res.msg);
                }
            }).fail(function(res){

            }).always(function(){
                // utils.loadHide();
            });
        }
        installApp();
    },

    // 普通版APP打包下载
    normalInstallAPP : function(){
        // 抛出下载进度条
        this.Progress().showProgressBox();
        // utils.loading();
        var installData = {
            ios_version : utils.getVersion(),
            device_type : utils.getDeviceType(),
            channelid : this.getChannelid()
        }
        var self = this;
        clearTimeout(packTimeout),packTimeout = null;
        //打包过程
        function installApp(){
            utils.get(API.plist2, installData, false).done(function(res){
                if(res.code == 0){
                    if(res.msg.data && res.msg.data.status != 0){
                        console.log('打包中...');
                        // 不停的打包
                        packTimeout = setTimeout(installApp, POLLING_INTERVAL);
                    }else{
                        //打包成功
                        self.Progress().hideProgressBox();
                        window.location.href = res.msg.data.aplist;
                        $(".normal-install").children("p").addClass("installSuccess").html("安装完成后，请前往桌面查看哦!");
                    }
                }else{
                    self.Progress().hideProgressBox();
                    return alert(res.msg);
                }
            }).fail(function(res){

            }).always(function(){
                // utils.loadHide();
            });
        }
        installApp();
    },

    // 获取渠道 channelid
    getChannelid : function(){
        return utils.cookie.get(channelid) || 100;
    },

    // 下载打包进度条
    Progress : function(){

        // 进度弹窗
    	var progressBox;
        var self = this;

        function showProgressBox() {
    		html = [
    			'<div class="pop-inner">',
    				'<p class="progress-head">',
    					'<span class="loading-number"></span>%',
    				'</p>',
    				'<div class="progress-bar">',
    					'<div class="total">',
    						'<span class="present"></span>',
    					'</div>',
    				'</div>',
    				'<p class="info">',
    					'正在配置下载，请不要关闭此页面',
    				'</p>',
    			'</div>'
    		].join('');

            progressBox = utils.createDiv(html);
            progressBox.classList.add('progress');
            updateProgress();
        }

        // 更新进度条
        var interval = null;
    	function updateProgress(){
    		if(!progressBox) {
    			return;
    		}

    		var loadingTxt = progressBox.querySelector('.loading-number');
    		var present = progressBox.querySelector('.present');
    		var info = progressBox.querySelector('.info');

    		var max = 98;
    		var curent = 1;

    		interval = setInterval(function(){
    			curent ++;
    			info.innerHTML = '正在配置下载，请不要关闭此页面';
    			var noTip = '<em>一大波果粉正在占领服务器，请稍后点击安装重试哦~无需再次支付~</em>'
    			if(curent == 98) {
    				clearTimeout(packTimeout);	// 停止打包进度请求
    				clearInterval(interval);
                    info.innerHTML = noTip;
    			}
    			loadingTxt.innerHTML = curent;
    			present.style.width = curent + '%';
    		}, 900);
    	}

    	function hideProgressBox(){
            clearTimeout(packTimeout);
            clearInterval(interval);
            utils.closeDiv();
    	}

        return {
        	showProgressBox : showProgressBox,
        	updateProgress  : updateProgress,
        	hideProgressBox : hideProgressBox
        }
    },

    // 在非safari原生浏览器的环境下，点击意见安装，显示复制的弹窗
    showSafariBox : function(){
        var html = [
                    '<div class="safari-wrap">',
                    	'<img src="img/safari.png">',
                    	'<p>Safari</p>',
                    '</div>',
					'<p class="info">仅支持在Safari浏览器内安装，请拷贝当前链接在Safari浏览器打开进行安装</p>',
					'<div class="btn-wrap clearfix">',
						'<span class="curr-location" contenteditable=true></span>',
						'<a href="javascript:void(0)" id="copy">复制</a>',
					'</div>'
        ];

        var nodeLink;

        safariBox= utils.createDiv(html.join(''));
        nodeLink = safariBox.querySelector('.curr-location');
        nodeLink.innerHTML = location.href;

        /**
         * 位置修正
         */
        nodeLink.addEventListener('focus', function(){
        	safariBox.style.display = "relative";
        }, false);

        nodeLink.addEventListener('blur', function(){
        	safariBox.style.display = "absolute";
        }, false);

        /**
         * 对于不支持复制选择功能的， 就直接扔出链接
         */
        var canUseSel = false;
        if(window.getSelection){
            canUseSel = true;
        }

        /**
         * 点击复制进行文本选择
         * @throws 在低版本的safari浏览器中不支持
         */
        safariBox.querySelector('#copy').addEventListener('click', function(){
            if(!canUseSel){
                utils.toast("浏览器不支持长按复制功能!");
                // utils.closeDiv();
                return false;
            }

            var doc = document,
               text = doc.querySelector('.curr-location'),
               range,
               selection;

           text.setAttribute("contenteditable", true);
           selection = window.getSelection();
           range = doc.createRange();
           range.selectNodeContents(text);
           selection.removeAllRanges();
           selection.addRange(range);
           //注意IE9-不支持textContent
           makeSelection(0, text.firstChild.textContent.length, 0, text.firstChild);

        },false);


        function makeSelection(start, end, child, parent) {
            var range = document.createRange();
            range.setStart(parent.childNodes[child] || parent, start);
            range.setEnd(parent.childNodes[child] || parent, end);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }

    },

    // 设置图文内容
    setTabStep : function(n){
        var $box = $(".step-box"),
            $item = $box.find(".step-item"),
            firstBox = $(".first-txt");
        if(n == 0 || n == 2){
            $item.show();
            firstBox.find("h6").html("海量游戏，免费下载，一键安装");
            firstBox.find("p").html("新游、热游、破解…国内外最新最热游戏一搜既有！");
            return;
        }else{
            $item.last().hide();
            firstBox.find("h6").html("正版游戏，一键安装");
            firstBox.find("p").html("国内外最新最热游戏即搜即装");
        }
    },

    ios : function(callback){
        return function(){
            if(!/iPhone|iPad|iPod|iOS/i.test(navigator.userAgent)){
                return utils.toast("该应用只支持IOS设备");
            }
            callback && callback.apply(this, arguments);
        }
    },

    // 在微信下，会引导用户
    weixin : function(){
        var html = '<div class="v-cover" id="vCover">\
            			<img src="/public/agent/ios/files/wxbg3.png"  />\
            		</div>';
        document.body.insertAdjacentHTML("beforeEnd", html);
        var vCover = utils.getId(".v-cover");
        vCover.classList.add('show');
        vCover.addEventListener("click", function(){
            this.classList.remove("show");
        }, false);
    },

    isShow : function(obj, hideClass, isShow){
        isShow ? obj.removeClass(hideClass) : obj.addClass(hideClass);
    },

    // 获取从客户
	shareParam : function(){
        function set(){
            //http://app.guopan.cn?uid=123456&channel=100&platform=ios&version=1.2.0&productid=109（用户已登录）
            //http://app.guopan.cn?channel=100&platform=ios&version=1.2.0&productid=109（用户未登录）
            var param = utils.getSearchParam;
            if(param && param.channel && param.platform && param.version && param.productid){
                return utils.cookie.set("shareParam", JSON.stringify(param), 1);
            }
            utils.cookie.get("shareParam") && utils.cookie.del("shareParam");
        }

        function get(){
            return utils.cookie.get("shareParam") && JSON.parse(utils.cookie.get("shareParam")) || "";
        }

        return {
            init : set,
            get : get
        }

	}

}


// 由于9-7号是苹果发布日，临时加个提示需求
function addTipShow(){
    var targetDate = new Date().getTime(); // 当前时间
    var startDate = new Date("2016-09-07 01:00:00").getTime();
    var endDate = new Date("2016-09-07 05:00:00").getTime();
    var $tip
    $(".timeoutTip").removeClass("isHide");
    if(targetDate > startDate && targetDate < endDate){
        $(".timeoutTip").removeClass("isHide");
    }

}

// init
$(function(){
    // 如果是在微信浏览器下
    // if(utils.isWeixin()){
    //     guopanIos.weixin();
    // }else{
        guopanIos.init();
    // }
})
