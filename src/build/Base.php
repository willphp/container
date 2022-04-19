<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: no-mind <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\container\build;
class Base implements \ArrayAccess {
	public $bindings = []; //绑定实例
	public $instances = []; //单例服务
	/**
	 * 服务绑定到容器
	 * @param      $name    服务名
	 * @param      $closure 返回服务对象的闭包函数
	 * @param bool $force   是否单例
	 */
	public function bind($name, $closure, $force = false) {
		$this->bindings[$name] = compact('closure', 'force');
	}
	/**
	 * 注册单例服务
	 * @param $name    服务
	 * @param $closure 闭包函数
	 */
	public function single($name, $closure) {
		$this->bind($name, $closure, true);
	}
	/**
	 * 单例服务
	 * @param string $name   名称
	 * @param mixed  $object 对象
	 */
	public function instance($name, $object) {
		$this->instances[$name] = $object;
	}
	/**
	 * 获取服务实例
	 * @param      $name  服务名
	 * @param bool $force 是否单例
	 * @return mixed|object
	 */
	public function make($name, $force = false) {
		if (isset($this->instances[$name])) {
			return $this->instances[$name];
		}
		$closure = $this->getClosure($name); //获得实现提供者
		$object = $this->build($closure); //获取实例
		if (isset($this->bindings[$name]['force']) && $this->bindings[$name]['force'] || $force) {
			$this->instances[$name] = $object; //单例绑定
		}
		return $object;
	}
	/**
	 * 获得实例实现
	 * @param $name 创建实例方式:类名或闭包函数
	 * @return mixed
	 */
	private function getClosure($name) {
		return isset($this->bindings[$name]) ? $this->bindings[$name]['closure'] : $name;
	}
	/**
	 * 依赖注入方式调用函数
	 * @param $function
	 * @return mixed
	 */
	public function callFunction($function) {
		$reflectionFunction = new \ReflectionFunction($function);
		$args = $this->getDependencies($reflectionFunction->getParameters());
		return $reflectionFunction->invokeArgs($args);
	}
	/**
	 * 反射执行方法并实现依赖注入
	 * @param $class  类
	 * @param $method 方法
	 * @return mixed
	 */
	public function callMethod($class, $method) {
		$reflectionMethod = new \ReflectionMethod($class, $method); //反射方法实例
		$args = $this->getDependencies($reflectionMethod->getParameters());	//解析方法参数
		return $reflectionMethod->invokeArgs($this->build($class), $args); //生成类并执行方法
	}
	/**
	 * 递归解析参数
	 * @param $parameters
	 * @return array
	 */
	public function getDependencies($parameters) {
		$dependencies = [];
		foreach ($parameters as $parameter) {
			$dependency = $parameter->getClass();
			if (is_null($dependency)) {
				$dependencies[] = $this->resolveNonClass($parameter);
			} else {
				$dependencies[] = $this->build($dependency->name);
			}
		}
		return $dependencies;
	}
	/**
	 * 生成服务实例
	 * @param mixed $className 生成方式 类或闭包函数
	 * @return object
	 */
	public function build($className) {
		if ($className instanceof \Closure) {
			return $className($this);
		}
		$reflector = new \ReflectionClass($className);
		if (!$reflector->isInstantiable()) {
			throw new \Exception($className.' cannot be instantiated.');
		}
		$constructor = $reflector->getConstructor();
		if (is_null($constructor)) {
			return new $className;
		}
		$parameters = $constructor->getParameters();
		$dependencies = $this->getDependencies($parameters);
		return $reflector->newInstanceArgs($dependencies);
	}
	/**
	 * 提取参数默认值
	 * @param $parameter
	 * @return mixed
	 */
	public function resolveNonClass($parameter) {
		if ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}
		throw new \Exception('Parameter has no default value.');
	}
	public function offsetExists($key) {
		return isset($this->bindings[$key]);
	}
	public function offsetGet($key) {
		return $this->make($key);
	}
	public function offsetSet($key, $value) {
		if (!$value instanceof \Closure) {
			$value = function () use ($value) {
				return $value;
			};
		}
		$this->bind($key, $value);
	}
	public function offsetUnset($key) {
		unset($this->bindings[$key], $this->instances[$key]);
	}
	public function __get($key) {
		return $this[$key];
	}
	public function __set($key, $value)	{
		$this[$key] = $value;
	}
}
