<?php

#[AllowDynamicProperties]
class OsModel {

	protected $error,
		$db;

	public $nice_names = [];
	protected $comparisons = array( '>=', '<=', '<', '>', '!=', 'LIKE' );
	protected $conditions = [];
	protected $limit = false;
	protected $offset = false;
	protected $select_args = [];
	protected $order_args = false;
	protected $group_args = false;
	protected $having_args = false;
	protected $joins = [];
	public $data_vars = [];
	public $first_level_data_vars = [];
	public $form_id = false;
	public $last_query = '';
	protected $meta_class = false;
	public $meta = false;
	public $table_name = '';
	public $join_attributes = [];

	function __construct( $id = false ) {
		$this->error = false;
		global $wpdb;
		$this->db = $wpdb;
		if ( $id ) {
			$this->load_by_id( $id );
		}
	}

	public function __get( $property ) {
		$method = "get_$property";
		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}
	}

	public function exists() {
		return ( isset( $this->id ) && ! empty( $this->id ) );
	}

	public function formatted_created_date( $format = false, $default = 'n/a' ) {
		if ( ! $format ) {
			$format = OsSettingsHelper::get_readable_date_format();
		}
		if ( property_exists( $this, 'created_at' ) && isset( $this->created_at ) && ! empty( $this->created_at ) ) {
			$date = new OsWpDateTime( $this->created_at );

			return $date->format( $format );
		} else {
			return $default;
		}
	}

	public function prepare( $query, $values ) {
		if ( empty( $values ) ) {
			return $query;
		} else {
			return $this->db->prepare( $query, $values );
		}
	}


	/**
	 *
	 * Clears all GROUP BY arguments
	 *
	 * @return $this OsModel
	 */
	public function clear_group_by(): OsModel {
		$this->group_args = '';

		return $this;
	}

	public function group_by( $group_args ) {
		if ( $this->group_args ) {
			$this->group_args = implode( ',', array( $this->group_args, $group_args ) );
		} else {
			$this->group_args = $group_args;
		}

		return $this;
	}

	public function get_group_args() {
		if ( $this->group_args ) {
			return 'GROUP BY ' . $this->group_args;
		} else {
			return '';
		}
	}

	public function clear_having(): OsModel {
		$this->having_args = '';

		return $this;
	}


	public function get_having_args(): string {
		if ( $this->having_args ) {
			return 'HAVING ' . $this->having_args;
		}
		return '';
	}

	public function having( $having_args ) {
		if ( $this->having_args ) {
			$this->having_args = implode( ',', array( $this->having_args, $having_args ) );
		} else {
			$this->having_args = $having_args;
		}

		return $this;
	}

	public function order_by( $order_args ) {
		if ( $this->order_args ) {
			$this->order_args = implode( ',', array( $this->order_args, $order_args ) );
		} else {
			$this->order_args = $order_args;
		}

		return $this;
	}

	public function get_order_args() {
		if ( $this->order_args ) {
			return 'ORDER BY ' . $this->order_args;
		} else {
			return '';
		}
	}

	public static function where_in_array_to_string( $array_of_values ) {
		$clean_string = '';
		if ( is_array( $array_of_values ) ) {
			$array_of_values = array_map( function ( $v ) {
				return "'" . esc_sql( $v ) . "'";
			}, $array_of_values );
			$clean_string    = ' (' . implode( ',', $array_of_values ) . ') ';
		}

		return $clean_string;
	}


	/**
	 * @param array $conditions
	 *
	 * @return $this
	 */
	public function filter_where_conditions( array $allowed_conditions ): OsModel {
		foreach ( $allowed_conditions as $condition_name => $allowed_condition_value ) {
			if ( empty( $this->conditions[ $condition_name ] ) ) {
				$this->conditions[ $condition_name ] = $allowed_condition_value;
			} else {
				// convert both to arrays to compare
				$current_value                       = is_array( $this->conditions[ $condition_name ] ) ? $this->conditions[ $condition_name ] : OsUtilHelper::explode_and_trim( $this->conditions[ $condition_name ] );
				$allowed_value                       = is_array( $allowed_condition_value ) ? $allowed_condition_value : OsUtilHelper::explode_and_trim( $allowed_condition_value );
				$this->conditions[ $condition_name ] = array_intersect( $current_value, $allowed_value );
			}
		}

		return $this;
	}

	public function where( $conditions ) {
		if ( empty( $conditions ) ) {
			return $this;
		}
		$this->conditions = array_merge( $this->conditions, $conditions );

		return $this;
	}

	public function where_in( $column, $array_of_values ) {
		$condition        = array( "{$column} IN " => $array_of_values );
		$this->conditions = array_merge( $this->conditions, $condition );

		return $this;
	}

	public function where_not_in( $column, $array_of_values ) {
		$condition        = array( "{$column} NOT IN " => $array_of_values );
		$this->conditions = array_merge( $this->conditions, $condition );

		return $this;
	}

	public function join( $table, $on_args, $type = '' ) {
		$this->joins[] = [
			'join_table'   => $table,
			'join_on_args' => $on_args,
			'join_type'    => in_array( $type, [ 'left', 'right' ] ) ? $type : ''
		];

		return $this;
	}

	public function get_join_string(): string {
		$join_query = '';
		if ( ! empty( $this->joins ) ) {
			foreach ( $this->joins as $join_data ) {
				if ( empty( $join_data['join_table'] ) || empty( $join_data['join_on_args'] ) ) {
					continue;
				}
				$join_query .= $join_data['join_type'] . ' JOIN ' . $join_data['join_table'] . ' ON ' . $this->build_join_args_query( $join_data['join_table'], $join_data['join_on_args'] );
			}
		}

		return $join_query;
	}

	private function build_join_args_query( $join_table, $join_on_args ) {
		$join_args_query_arr = [];
		foreach ( $join_on_args as $column_one => $column_two ) {
			if ( is_array( $column_two ) ) {
				$in_values             = implode( ',', $column_two );
				$join_args_query_arr[] = "{$join_table}.{$column_one} IN ({$in_values})";
			} else {
				$join_args_query_arr[] = "{$join_table}.{$column_one} = {$column_two}";
			}
		}

		return implode( ' AND ', $join_args_query_arr );
	}


	/**
	 *
	 * Clears all SELECT arguments
	 *
	 * @return $this OsModel
	 */
	public function clear_select(): OsModel {
		$this->select_args = [];

		return $this;
	}

	/**
	 *
	 * Adds arguments to SELECT query
	 *
	 * @param $select_args Array|string or comma separated String of arguments
	 *
	 * @return $this OsModel
	 */
	public function select( $select_args ): OsModel {
		if ( ! is_array( $select_args ) ) {
			$select_args = OsUtilHelper::explode_and_trim( $select_args );
		}
		if ( ! empty( $select_args ) ) {
			$this->select_args = array_merge( $this->select_args, $select_args );
		}

		return $this;
	}

	public function build_select_args_string(): string {
		$select_args = $this->get_select_args();
		if ( empty( $select_args ) ) {
			return '*';
		} else {
			return implode( ',', array_unique( $this->select_args ) );
		}
	}

	public function get_select_args(): array {
		return $this->select_args;
	}

	/**
	 * Eager load meta key-value pairs associated with this model
	 *
	 * @param array $meta_keys
	 *
	 * @return $this
	 */
	public function with_meta( array $meta_keys = [] ): OsModel {
		$this->meta = [];
		$meta_class = $this->meta_class;

		if ( $this->exists() && $meta_class && class_exists( $meta_class ) ) {
			/** @var OsMetaModel $meta_object */
			$meta_object = new $meta_class();
			if ( ! empty( $meta_keys ) ) {
				foreach ( $meta_keys as $meta_key ) {
					$this->meta[] = [ $meta_key => $meta_object->get_by_key( $meta_key, $this->id ) ];
				}
			} else {
				$this->meta = $meta_object->get_by_object_id( $this->id );
			}
		}

		return $this;
	}

	public function set_limit( $limit ) {
		$this->limit = $limit;

		return $this;
	}

	public function count() {
		$count = $this->clear_select()->clear_group_by()->clear_having()->select( 'COUNT(DISTINCT(' . $this->table_name . '.id)) as total' )->set_limit( 1 )->get_results();
		$total = ( $count ) ? $count->total : 0;

		return $total;
	}


	public function set_offset( $offset ) {
		$this->offset = $offset;

		return $this;
	}

	protected function with_table_name( $column ) {
		if ( ! is_numeric( $column ) && ! in_array( $column, [
				'AND',
				'OR'
			] ) && ( strpos( $column, '(' ) === false ) && ( strpos( $column, '.' ) === false ) ) {
			return $this->table_name . '.' . $column;
		} else {
			return $column;
		}
	}

	protected function build_conditions_query( $conditions, $logical_operator = 'AND' ) {
		$where_conditions = [];
		$where_values     = [];
		$sql_query        = '';
		$index            = 0;
		if ( $conditions ) {
			foreach ( $conditions as $column => $value ) {
				$temp_query = false;
				if ( $column == 'OR' || $column == 'AND' ) {
					$sql_query             .= '(';
					$conditions_and_values = $this->build_conditions_query( $value, $column );
					$sql_query             .= $conditions_and_values[0];
					$where_values          = array_merge( $where_values, $conditions_and_values[1] );
					$sql_query             .= ')';
				} else {
					// Check if its a comparison condition e.g. <, >, <=, >= etc...
					foreach ( $this->comparisons as $comparison ) {
						if ( strpos( $column, $comparison ) ) {
							$column     = str_replace( $comparison, '', $column );
							$temp_query = $this->with_table_name( $column ) . $comparison . ' %s';
						}
					}
					// WHERE IN query
					if ( strpos( $column, ' NOT IN' ) && is_array( $value ) ) {
						$temp_query = $this->with_table_name( $column ) . OsModel::where_in_array_to_string( $value );

					} elseif ( strpos( $column, ' IN' ) && is_array( $value ) ) {
						$temp_query = $this->with_table_name( $column ) . OsModel::where_in_array_to_string( $value );
					} elseif ( is_array( $value ) && ( isset( $value['OR'] ) || isset( $value['AND'] ) ) ) {
						// IS ARRAY AND OR
						foreach ( $value as $condition_and_or => $condition_values ) {

							$temp_query  .= '(';
							$sub_queries = [];
							foreach ( $condition_values as $condition_key => $condition_value ) {
								if ( is_string( $condition_key ) && is_string( $column ) ) {
									$temp_key       = $this->with_table_name( $column ) . $condition_key;
									$sub_conditions = [ $temp_key => $condition_value ];
								} elseif ( is_string( $condition_key ) ) {
									$sub_conditions = [ $this->with_table_name( $condition_key ) => $condition_value ];
								} else {
									$sub_conditions = [ $column => $condition_value ];
								}
								$conditions_and_values = $this->build_conditions_query( $sub_conditions, $condition_and_or );
								$sub_queries[]         = $conditions_and_values[0];
								$where_values          = array_merge( $where_values, $conditions_and_values[1] );
							}
							$temp_query .= implode( ' ' . $condition_and_or . ' ', $sub_queries );
							$temp_query .= ')';
						}
					} elseif ( $value === 'IS NULL' ) {
						// IS NULL
						$temp_query = $this->with_table_name( $column ) . ' IS NULL ';
					} elseif ( $value === 'IS NOT NULL' ) {
						// IS NOT NULL
						$temp_query = $this->with_table_name( $column ) . ' IS NOT NULL ';
					} elseif ( is_array( $value ) && ! empty( $value ) ) {
						$temp_query = $this->with_table_name( $column ) . ' IN ' . OsModel::where_in_array_to_string( $value );
					} else {
						// Add to list of query values
						if ( is_array( $value ) ) {
							$where_values[] = OsModel::where_in_array_to_string( $value );
						} else {
							$where_values[] = $value;
						}
					}
					if ( $temp_query ) {
						$sql_query .= $temp_query;
					} else {
						$sql_query .= $this->with_table_name( $column ) . '= %s';
					}
				}
				$index ++;
				if ( $index < count( $conditions ) ) {
					$sql_query .= ' ' . $logical_operator . ' ';
				}
			}
		}

		return array( $sql_query, $where_values );
	}


	public function escape_by_ref( &$string ) {
		$this->db->escape_by_ref( $string );
	}

	public function get_results( $results_type = OBJECT ) {
		$conditions_and_values = $this->build_conditions_query( $this->conditions );
		if ( $conditions_and_values[0] ) {
			$where_query = 'WHERE ' . $conditions_and_values[0];
		} else {
			$where_query = '';
		}
		if ( $this->limit ) {
			$limit_query                = ' LIMIT %d';
			$conditions_and_values[1][] = $this->limit;
		} else {
			$limit_query = '';
		}


		if ( $this->offset ) {
			$offset_query               = ' OFFSET %d';
			$conditions_and_values[1][] = $this->offset;
		} else {
			$offset_query = '';
		}

		$query = 'SELECT ' . $this->build_select_args_string() . ' FROM ' . $this->table_name . ' ' . $this->get_join_string() . ' ' . $where_query . ' ' . $this->get_group_args() . ' ' . $this->get_having_args() . ' ' . $this->get_order_args() . ' ' . $limit_query . ' ' . $offset_query;

		$this->last_query = vsprintf( $query, $conditions_and_values[1] );
		OsDebugHelper::log_query( $this->last_query );

		$items = $this->db->get_results(
			$this->prepare( $query, $conditions_and_values[1] )
			, $results_type );

		if ( ( $this->limit == 1 ) && isset( $items[0] ) ) {
			$items = $items[0];
		}

		return $items;
	}


	public function get_query_results( $query, $values = [], $results_type = OBJECT ) {
		$this->last_query = $query;
		$items            = $this->db->get_results(
			$this->prepare( $query, $values )
			, $results_type );
		OsDebugHelper::log_query( $query );

		return $items;
	}


	public function reset_conditions() {
		$this->conditions = [];
	}


	/**
	 * @param $query
	 * @param $values
	 *
	 * @return static|static[]
	 */
	public function get_results_as_models( $query = false, $values = [] ) {
		if ( $query ) {
			$items = $this->get_query_results( $query, $values );
		} else {
			$items = $this->get_results();
		}
		$models = [];
		if ( empty( $items ) ) {
			return [];
		}
		if ( $this->limit == 1 ) {
			$items = [ $items ];
		}
		foreach ( $items as $item ) {
			$current_class_name = get_class( $this );
			$model              = new $current_class_name();
			foreach ( $item as $prop_name => $prop_value ) {
				$model->$prop_name = $prop_value;
			}
			/**
			 * A child of <code>OsModel</code> is about to be added to the result set
			 *
			 * @param {OsModel} $model Instance of model that should be filtered
			 * @returns {OsModel} Instance of model that has been filtered
			 *
			 * @since 1.0.0
			 * @hook latepoint_get_results_as_models
			 *
			 */
			$model = apply_filters( 'latepoint_get_results_as_models', $model );
			if ( $model ) {
				$models[] = $model;
			}
		}
		$this->reset_conditions();
		if ( $this->limit == 1 && isset( $models[0] ) ) {
			$models = $models[0];
		}

		return $models;
	}

	public function filter_allowed_records(): OsModel {
		return $this;
	}

	public function get_image_url( $size = 'thumbnail' ) {
		$url = OsImageHelper::get_image_url_by_id( $this->image_id, $size );

		return $url;
	}

	public function set_data( $data, $role = 'admin', $sanitize = true ) {
		$data = $this->prepare_data_before_it_is_set( $data );
		/**
		 * Data/Params are being prepared to be set on a child of <code>OsModel</code>
		 *
		 * @param {OsModel} $this Instance of model that data is to be set on
		 * @param {array} $data Array of data/params to be set
		 *
		 * @since 1.0.0
		 * @hook latepoint_model_prepare_set_data
		 *
		 */
		do_action( 'latepoint_model_prepare_set_data', $this, $data );
		if ( is_array( $data ) ) {
			// array passed
			// if ID is passed and model not loaded from db yet - load data from db
			if ( isset( $data['id'] ) && is_numeric( $data['id'] ) && property_exists( $this, 'id' ) && $this->is_new_record() ) {
				$this->load_by_id( $data['id'] );
			}
			foreach ( $this->get_allowed_params( $role ) as $param ) {
				if ( isset( $data[ $param ] ) ) {
					$this->$param = $sanitize ? $this->sanitize_param( $param, $data[ $param ] ) : $data[ $param ];
				}
			}
		} else {
			// object passed
			// if ID is passed and model not loaded from db yet - load data from db
			if ( isset( $data->id ) && is_numeric( $data->id ) && property_exists( $this, 'id' ) && $this->is_new_record() ) {
				$this->load_by_id( $data->id );
			}
			foreach ( $this->get_allowed_params( $role ) as $param ) {
				if ( isset( $data->$param ) ) {
					$this->$param = $sanitize ? $this->sanitize_param( $param, $data->$param ) : $data->$param;
				}
			}
		}
		/**
		 * Data/Params have been set on a child of <code>OsModel</code>
		 *
		 * @param {OsModel} $this Instance of model that data was set on
		 * @param {array} $data Array of data/params that was set
		 *
		 * @since 1.0.0
		 * @hook latepoint_model_set_data
		 *
		 */
		do_action( 'latepoint_model_set_data', $this, $data );
		$this->after_data_was_set( $data );

		return $this;
	}

	/**
	 * @return void
	 *
	 * Useful for child classes, to do something after a data is set
	 */
	public function after_data_was_set( $data ) {

	}


	/**
	 * @return void
	 *
	 * Useful for child classes, to do something after a data is set
	 */
	public function prepare_data_before_it_is_set( $data ) {
		return $data;
	}


	public function delete_where( $where = false, $where_format = null ) {
		if ( is_array( $where ) && $this->db->delete( $this->table_name, $where, $where_format ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function delete( $id = false ) {
		if ( ! $id && isset( $this->id ) ) {
			$id = $this->id;
		}
		if ( $id && $this->db->delete( $this->table_name, array( 'id' => $id ), array( '%d' ) ) ) {
			/**
			 * A child of <code>OsModel</code> has been deleted
			 *
			 * @param {OsModel} $this Instance of model that has been deleted
			 * @param {integer} $id ID of model instance that has been deleted
			 *
			 * @since 4.6.3
			 * @hook latepoint_model_deleted
			 *
			 */
			do_action( 'latepoint_model_deleted', $this, $id );

			return true;
		} else {
			return false;
		}
	}


	public function load_from_row_data( $row_data ) {
		foreach ( $row_data as $key => $field ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $field;
			}
		}
	}

	public function load_by_id( $id ) {
		if ( filter_var( $id, FILTER_VALIDATE_INT ) === false ) {
			return false;
		}
		$query      = $this->prepare( 'SELECT ' . $this->build_select_args_string() . ' FROM ' . $this->table_name . ' WHERE id = %d', $id );
		$result_row = $this->db->get_row( $query, ARRAY_A );

		if ( $result_row ) {
			foreach ( $result_row as $row_key => $row_value ) {
				if ( property_exists( $this, $row_key ) ) {
					$this->$row_key = $row_value;
				}
			}

			/**
			 * A child of <code>OsModel</code> has been loaded from the DB by its ID
			 *
			 * @param {OsModel} $this Instance of model that has been loaded
			 * @returns {OsModel} Instance of model that has been filtered
			 *
			 * @since 1.0.0
			 * @hook latepoint_model_loaded_by_id
			 *
			 */
			return apply_filters( 'latepoint_model_loaded_by_id', $this );
		} else {
			return false;
		}
	}


	/**
	 *
	 * Generates an ID that is used in a form for quick editing. Returns ID if exists or returns a "new_HASH" to be used
	 * as ID to indicate that it's a new record
	 *
	 * @return string
	 */
	public function get_form_id(): string {
		if ( $this->is_new_record() ) {
			if ( empty( $this->form_id ) ) {
				$this->form_id = OsUtilHelper::generate_form_id();
			}
		} else {
			$this->form_id = $this->id;
		}

		return $this->form_id;
	}


	public function is_new_record() {
		if ( $this->id ) {
			return false;
		} else {
			return true;
		}
	}

	public function get_field( $field_name ) {
		return $this->$field_name;
	}

	public function set_field( $field_name, $field_value ) {
		$this->$field_name = $field_value;
	}

	protected function before_save() {

	}

	protected function before_create() {

	}

	// updates array of attributes
	public function update_attributes( $data, $sanitize = true ) {
		if ( $this->is_new_record() ) {
			return false;
		}
		$prepared_data = [];
		foreach ( $data as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				if ( $sanitize && array_key_exists( $key, $this->params_to_sanitize() ) ) {
					$value = OsParamsHelper::sanitize_param( $value, $this->params_to_sanitize()[ $key ] );
				}
				$this->$key = $value;
				// encrypt value if it needs to be encrypted, however the model object itself stores an un-encrypted value
				if ( in_array( $key, $this->encrypted_params() ) ) {
					$value = OsEncryptHelper::encrypt_value();
				}
				$prepared_data[ $key ] = $value;
			}
		}
		if ( empty( $prepared_data ) ) {
			return false;
		} else {
			if ( property_exists( $this, 'updated_at' ) ) {
				$prepared_data['updated_at'] = OsTimeHelper::now_datetime_in_format( "Y-m-d H:i:s" );
			}
			if ( false === $this->db->update( $this->table_name, $prepared_data, array( 'id' => $this->id ) ) ) {
				$this->add_error( 'update_error', $this->db->last_error );

				return false;
			} else {
				OsDebugHelper::log_query( $this->db->last_query );

				return true;
			}
		}
	}

	protected function set_defaults() {

	}

	// searches list of params that need to be sanitised and returns sanitised value
	protected function sanitize_param( $param_name, $value ) {
		if ( $this->params_to_sanitize() && is_array( $this->params_to_sanitize() ) && array_key_exists( $param_name, $this->params_to_sanitize() ) ) {
			$value = OsParamsHelper::sanitize_param( $value, $this->params_to_sanitize()[ $param_name ] );
		}

		return $value;
	}

	public function save( $alternative_validation = false, $skip_validation = false ) {
		try {
			$this->set_defaults();
			$this->before_save();
			if ( $skip_validation || $this->validate( $alternative_validation ) ) {
				if ( property_exists( $this, 'updated_at' ) ) {
					$this->updated_at = OsTimeHelper::now_datetime_in_format( "Y-m-d H:i:s" );
				}
				if ( $this->is_new_record() ) {
					// New Record (insert)
					$this->before_create();
					if ( property_exists( $this, 'created_at' ) ) {
						$this->created_at = OsTimeHelper::now_datetime_in_format( "Y-m-d H:i:s" );
					}
					if ( false === $this->db->insert( $this->table_name, $this->get_params_to_save_with_values() ) && property_exists( $this, 'id' ) ) {
						$this->add_error( 'insert_error', $this->db->last_error );

						return false;
					} else {
						OsDebugHelper::log_query( $this->db->last_query );
						$this->id = $this->db->insert_id;
					}
				} else {
					// Existing record (update)
					if ( false === $this->db->update( $this->table_name, $this->get_params_to_save_with_values(), array( 'id' => $this->id ) ) ) {
						$this->add_error( 'update_error', $this->db->last_error );

						return false;
					} else {
						OsDebugHelper::log_query( $this->db->last_query );
					}
				}
				/**
				 * A child of <code>OsModel</code> has been saved to the DB
				 *
				 * @param {OsModel} $this Instance of model that has been saved
				 *
				 * @since 1.0.0
				 * @hook latepoint_model_save
				 *
				 */
				do_action( 'latepoint_model_save', $this );
			} else {
				return false;
			}

			return true;
		} catch ( Exception $e ) {
			$this->add_error( 'save_exception', $e->getMessage() );

			return false;
		}
	}


	protected function get_property_nice_name( $property ) {
		if ( isset( $this->nice_names[ $property ] ) ) {
			return $this->nice_names[ $property ];
		} else {
			return ucwords( str_replace( "_", " ", $property ) );
		}
	}

	protected function get_params_to_save_with_values( $role = 'admin' ) {
		$params_to_save             = $this->get_params_to_save( $role );
		$params_to_save_with_values = [];

		foreach ( $params_to_save as $param_name ) {
			if ( property_exists( $this, $param_name ) ) {
				if ( $param_name == 'id' && empty( $this->id ) ) {
					// ignore this param if its ID and is not set
				} else {
					$params_to_save_with_values[ $param_name ] = $this->prepare_param( $param_name, $this->$param_name );
				}
			}
		}
		if ( property_exists( $this, 'updated_at' ) && isset( $this->updated_at ) ) {
			$params_to_save_with_values['updated_at'] = $this->updated_at;
		}
		if ( property_exists( $this, 'created_at' ) && isset( $this->created_at ) ) {
			$params_to_save_with_values['created_at'] = $this->created_at;
		}

		return $params_to_save_with_values;
	}


	protected function is_encrypted_param( $param_name ) {
		return in_array( $param_name, $this->encrypted_params( $param_name ) );
	}

	protected function prepare_param( $param_name, $value ) {
		if ( ! empty( $value ) ) {
			if ( $this->is_encrypted_param( $param_name ) ) {
				$value = OsEncryptHelper::encrypt_value( $value );
			} else {
				$value = $value;
			}
		}

		return $value;
	}

	protected function encrypted_params() {
		return [];
	}

	protected function params_to_sanitize() {
		return [];
	}

	public function generate_first_level_data_vars() : array{
		return [];
	}

	public function generate_data_vars(): array {
		return [];
	}

	public function get_data_vars( $force_regenerate = false ): array {
		$data = ( $force_regenerate || empty( $this->data_vars ) ) ? $this->generate_data_vars() : $this->data_vars;

		return apply_filters( 'latepoint_model_view_as_data', $data, $this );
	}

	public function get_first_level_data_vars( $force_regenerate = false ): array {
		$data = ( $force_regenerate || empty( $this->first_level_data_vars ) ) ? $this->generate_first_level_data_vars() : $this->first_level_data_vars;

		return apply_filters( 'latepoint_model_view_as_first_level_data', $data, $this );
	}

	protected function properties_to_query(): array {
		$properties = [];

		return $properties;
	}

	public function get_properties_to_query(): array {
		$properties = $this->properties_to_query();

		/**
		 * List of model properties that are allowed to be queried by the condition form in processes
		 *
		 * @param {array} $properties List of model properties allowed to be queried
		 * @param {OsModel} $this Instance of model that properties will be available for
		 * @returns {array} List of model properties that are allowed to be queried
		 *
		 * @since 4.7.0
		 * @hook latepoint_model_properties_to_query
		 *
		 */
		return apply_filters( 'latepoint_model_properties_to_query', $properties, $this );
	}

	// params that are allowed to be mass assigned using set_data method
	protected function allowed_params( $role = 'admin' ) {
		$allowed_params = [];

		return $allowed_params;
	}

	protected function params_to_save( $role = 'admin' ) {
		$allowed_params = [];

		return $allowed_params;
	}

	public function get_params_to_save( $role = 'admin' ) {
		return $this->params_to_save( $role );
	}

	public function get_allowed_params( $role = 'admin' ) {
		$allowed_params = $this->allowed_params( $role );

		/**
		 * List of model params that are allowed to be mass assigned to a child of <code>OsModel</code>
		 *
		 * @param {array} $allowed_params List of model params being filtered
		 * @param {OsModel} $this Instance of model that the allowed params apply to
		 * @param {string} $role User role that the allowed params apply to
		 * @returns {array} List of model params that are allowed to be mass assigned
		 *
		 * @since 1.0.0
		 * @hook latepoint_model_allowed_params
		 *
		 */
		return apply_filters( 'latepoint_model_allowed_params', $allowed_params, $this, $role );
	}






	// -------------------------
	// Error handling
	// -------------------------


	// CLEAR
	protected function clear_error() {
		$this->error = false;
	}


	// ADD
	public function add_error( $code, $error_message = 'Field is not valid.', $data = '' ) {
		if ( is_array( $error_message ) ) {
			$error_message = implode( ', ', $error_message );
		}
		if ( is_wp_error( $this->get_error() ) ) {
			$this->get_error()->add( $code, $error_message, $data );
		} else {
			$this->error = new WP_Error( $code, $error_message, $data );
		}
	}


	// GET DATA
	public function get_error_data( $code ) {
		if ( is_wp_error( $this->get_error() ) ) {
			return $this->get_error()->get_error_data( $code );
		} else {
			return false;
		}
	}

	// GET
	public function get_error() {
		return $this->error;
	}


	// CHECK
	public function has_validation_error() {
		if ( is_wp_error( $this->get_error() ) && $this->get_error()->get_error_messages( 'validation' ) ) {
			return true;
		} else {
			return false;
		}
	}


	// GET MESSAGES
	public function get_error_messages( $code = false ) {
		if ( is_wp_error( $this->get_error() ) ) {
			return $this->get_error()->get_error_messages( $code );
		} else {
			return [];
		}
	}




	// -------------------------
	// Validations
	// -------------------------

	public function validate( $alternative_validation = false, $skip_properties = [] ) : bool {
		$this->clear_error();
		foreach ( $this->properties_to_validate( $alternative_validation ) as $property_name => $validations ) {
			if($skip_properties && in_array( $property_name, $skip_properties )) continue;
			foreach ( $validations as $validation ) {
				$validation_function = 'validates_' . $validation;
				if ( ! method_exists( $this, $validation_function ) ) {
					continue;
				}
				$validation_result = $this->$validation_function( $property_name );
				if ( is_wp_error( $validation_result ) ) {
					$this->add_error( 'validation', $validation_result->get_error_message( $property_name ) );
				}
			}
		}
		/**
		 * Custom validations to apply to a child of <code>OsModel</code>
		 *
		 * @param {OsModel} $this Instance of model to apply custom validations to
		 * @param {bool} $alternative_validation True if applying alternative validations, false otherwise
		 *
		 * @since 1.0.0
		 * @hook latepoint_model_validate
		 *
		 */
		do_action( 'latepoint_model_validate', $this, $alternative_validation, $skip_properties );
		if ( $this->has_validation_error() ) {
			return false;
		} else {
			return true;
		}
	}


	protected function properties_to_validate() {
		return [];
	}

	protected function validates_email( $property ) {
		if ( isset( $this->$property ) && ! empty( $this->$property ) && OsUtilHelper::is_valid_email( $this->$property ) ) {
			return true;
		} else {
			// translators: %s is the property name for a model
			return new WP_Error( $property, sprintf( __( '%s is not valid', 'latepoint' ), $this->get_property_nice_name( $property ) ) );
		}
	}

	protected function validates_presence( $property ) {
		$validation_result = ( isset( $this->$property ) && ! empty( $this->$property ) );
		if ( $validation_result ) {
			return true;
		} else {
			// translators: %s is the property name for a model
			return new WP_Error( $property, sprintf( __( '%s can not be blank', 'latepoint' ), $this->get_property_nice_name( $property ) ) );
		}
	}

	protected function validates_uniqueness( $property ) {
		if ( isset( $this->$property ) && ! empty( $this->$property ) ) {
			if ( $this->is_new_record() ) {
				$query = $this->prepare( 'SELECT %i FROM %i WHERE %i = %s LIMIT 1', [
					$property,
					$this->table_name,
					$property,
					$this->$property
				] );
			} else {
				$query = $this->prepare( 'SELECT %i FROM %i WHERE %i = %s AND id != %d LIMIT 1', [
					$property,
					$this->table_name,
					$property,
					$this->$property,
					$this->id
				] );
			}
			$items = $this->db->get_results( $query, ARRAY_A );
			if ( $items ) {
				// translators: %s is the property name for a model
				return new WP_Error( $property, sprintf( __( '%s has to be unique', 'latepoint' ), $this->get_property_nice_name( $property ) ) );
			}
		}

		return true;
	}

	public function get_validations_for_property( string $property ): array {
		$validations = $this->properties_to_validate();

		return $validations[ $property ] ?? [];
	}


	public function format_created_datetime_rfc3339() {
		$datetime = OsTimeHelper::date_from_db( $this->created_at );
		if ( ! $datetime ) {
			return 'invalid date';
		}
		$datetime->setTimezone( new DateTimeZone( "UTC" ) );

		return $datetime->format( \DateTime::RFC3339 );
	}

}