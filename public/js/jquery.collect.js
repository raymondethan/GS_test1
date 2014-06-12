(function () {
	function collect(collection, property, type) {
		return collection.map(function () {
			if (type === 'method') {
				return jQuery(this)[property]();
			}
			return jQuery(this)[type](property);
		}).get();
	}
	
	jQuery.fn.collectAttr = function collectAttr(attribute) {
		return collect(this, attribute, 'attr');
	};
	
	jQuery.fn.collectProp = function collectProp(property) {
		return collect(this, property, 'prop');
	};
	
	jQuery.fn.collectMethod = function collectMethod(method) {
		return collect(this, method, 'method');
	};
})();