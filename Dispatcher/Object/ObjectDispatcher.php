<?php
namespace MagnusDispatcher\Dispatcher\Object {
	
	class ObjectDispatcher {

		public $logger;

		public function __construct($context, $logger = null) {

			$this->logger = $logger;

			if (isset($context['debug']) && $context['debug'] && $this->logger) {
				$this->logger->debug('Object Dispatcher initialized');
			}

		}

		public function __invoke($context, $obj, $chunk) {

			/* 
			 * First off, we have to determine whether it's an object or if it's a static resource.
			 */

			if (!is_object($obj)) {
				// It's not an object currently so it's either a reference to one or a static resource
				if (!class_exists($obj)) {
					if ($context['debug'] && $this->logger) {
						$this->logger->debug('Not an object reference, must be a function or static value. Refusing to proceed', [
							'passed'  => $obj,
							'context' => $context
						]);
					}

					return $obj;
				}

				if ($context['debug'] && $this->logger) {
		            $this->logger->debug('Instantiating routed class reference', [
		                'context' => $context,
		                'current' => $obj
		            ]);
		            
		        }

				$obj = new $obj($context);
			}

			if (in_array($chunk, get_class_methods($obj))) {

				if ($context['debug'] && $this->logger) {
			        $this->logger->debug('Generating dispatch response from obj->method', [
			        	'context' => $context,
			            'obj'     => $obj,
			            'method'  => $chunk
			        ]);
			        
	        	}

				return $obj->$chunk();

			} 

			if (in_array('__invoke', get_class_methods($obj))) {

				if ($context['debug'] && $this->logger) {
			        $this->logger->debug('Generating dispatch response from obj->__invoke', [
			        	'context' => $context,
			            'obj'     => $obj
			        ]);
			        
	        	}

				return $obj->__invoke();

			}

			if ($context['debug'] && $this->logger) {
		        $this->logger->debug('Unsure what to do with this so we give up and return the original object', [
		        	'context' => $context,
		            'obj'     => $obj
		        ]);
		        
        	}

        	return $obj;
		}

	}

}