<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KT_Member {

	/**
	 * Retorna todos os colaboradores, com join na unidade sempre presente.
	 * @param int $location_id   0 = todas as unidades
	 * @param int $position_id   0 = todas as funções
	 */
	public static function get_all( $location_id = 0, $position_id = 0 ) {
		global $wpdb;
		$conditions = [];
		if ( $location_id ) $conditions[] = $wpdb->prepare( 'm.location_id = %d', $location_id );
		if ( $position_id ) $conditions[] = $wpdb->prepare( 'm.position_id = %d', $position_id );
		$where = $conditions ? 'WHERE ' . implode( ' AND ', $conditions ) : '';
		return $wpdb->get_results(
			"SELECT m.*, u.display_name, u.user_email,
			        l.name AS location_name,
			        pos.name AS position_name,
			        NULLIF(TRIM(CONCAT(
			            COALESCE(umf.meta_value, ''), ' ',
			            COALESCE(uml.meta_value, '')
			        )), '') AS full_name
			 FROM {$wpdb->prefix}kt_members m
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 LEFT JOIN {$wpdb->prefix}kt_locations l ON l.id = m.location_id
			 LEFT JOIN {$wpdb->prefix}kt_positions pos ON pos.id = m.position_id
			 LEFT JOIN {$wpdb->usermeta} umf ON umf.user_id = u.ID AND umf.meta_key = 'first_name'
			 LEFT JOIN {$wpdb->usermeta} uml ON uml.user_id = u.ID AND uml.meta_key = 'last_name'
			 $where
			 ORDER BY u.display_name ASC"
		);
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT m.*, u.display_name, u.user_email, u.user_login
			 FROM {$wpdb->prefix}kt_members m
			 JOIN {$wpdb->users} u ON u.ID = m.user_id
			 WHERE m.id = %d",
			$id
		) );
	}

	public static function get_by_user_id( $user_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kt_members WHERE user_id = %d", $user_id
		) );
	}

	/**
	 * Cria um colaborador. Se existing_user_id estiver definido, vincula ao usuário existente.
	 * Caso contrário, cria um novo usuário WordPress.
	 *
	 * @return int|WP_Error  ID do colaborador ou erro
	 */
	public static function create( $data ) {
		global $wpdb;

		if ( ! empty( $data['existing_user_id'] ) ) {
			$user_id = absint( $data['existing_user_id'] );
			if ( ! get_user_by( 'id', $user_id ) ) {
				return new WP_Error( 'invalid_user', 'Usuário não encontrado.' );
			}
			// Verifica se já é colaborador
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}kt_members WHERE user_id = %d", $user_id ) ) ) {
				return new WP_Error( 'already_member', 'Este usuário já está cadastrado como colaborador.' );
			}
		} else {
			// Validação básica
			if ( empty( $data['email'] ) || empty( $data['username'] ) || empty( $data['password'] ) ) {
				return new WP_Error( 'missing_fields', 'Preencha nome, e-mail, usuário e senha para criar um novo usuário.' );
			}
			$user_id = wp_create_user(
				sanitize_user( $data['username'] ),
				$data['password'],
				sanitize_email( $data['email'] )
			);
			if ( is_wp_error( $user_id ) ) return $user_id;

			wp_update_user( [
				'ID'         => $user_id,
				'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
				'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
			] );
			$user = new WP_User( $user_id );
			$user->set_role( 'kt_staff' );
		}

		$location_id = absint( $data['location_id'] ?? 0 );
		$position_id = ! empty( $data['position_id'] ) ? absint( $data['position_id'] ) : null;
		update_user_meta( $user_id, 'kt_location_id', $location_id );

		$wpdb->insert( $wpdb->prefix . 'kt_members', [
			'user_id'     => $user_id,
			'location_id' => $location_id,
			'position_id' => $position_id,
			'hire_date'   => ! empty( $data['hire_date'] )   ? sanitize_text_field( $data['hire_date'] )   : null,
			'birth_date'  => ! empty( $data['birth_date'] )  ? sanitize_text_field( $data['birth_date'] )  : null,
		] );

		return $wpdb->insert_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;
		$member = self::get( $id );
		if ( ! $member ) return false;

		$location_id = absint( $data['location_id'] ?? 0 );
		$position_id = ! empty( $data['position_id'] ) ? absint( $data['position_id'] ) : null;
		update_user_meta( $member->user_id, 'kt_location_id', $location_id );

		// birth_date: usa o valor do POST se presente, senão preserva o existente
		if ( array_key_exists( 'birth_date', $data ) ) {
			$birth_date = ! empty( $data['birth_date'] ) ? sanitize_text_field( $data['birth_date'] ) : null;
		} else {
			$birth_date = $member->birth_date ?? null;
		}

		$wpdb->update(
			$wpdb->prefix . 'kt_members',
			[
				'location_id' => $location_id,
				'position_id' => $position_id,
				'hire_date'   => ! empty( $data['hire_date'] ) ? sanitize_text_field( $data['hire_date'] ) : null,
				'birth_date'  => $birth_date,
			],
			[ 'id' => absint( $id ) ]
		);
		return true;
	}

	public static function delete( $id ) {
		global $wpdb;
		$id = absint( $id );
		// Cascade: remove all training data before deleting the member record
		$wpdb->delete( $wpdb->prefix . 'kt_progress',     [ 'member_id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'kt_enrollments',  [ 'member_id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'kt_quiz_results', [ 'member_id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'kt_certificates', [ 'member_id' => $id ] );
		$wpdb->delete( $wpdb->prefix . 'kt_members',      [ 'id' => $id ] );
	}

	/**
	 * Importa colaboradores a partir de um arquivo CSV.
	 * Colunas esperadas (separadas por ; ou ,): NOME | E-MAIL | UNIDADE | FUNÇÃO
	 *
	 * @param string $file_path   Caminho para o arquivo CSV (tmp_name do upload)
	 * @param int    $default_loc ID de unidade padrão (0 = resolver pelo CSV)
	 * @return array { created, skipped, errors[] }
	 */
	/**
	 * @param string $file_path   Caminho para o arquivo CSV (tmp_name do upload)
	 * @param int    $default_loc ID de unidade padrão (0 = resolver pelo CSV)
	 * @param bool   $send_email       Se true, envia e-mail de boas-vindas para cada novo usuário criado
	 * @param string $default_password Senha fixa para todos os usuários. Se vazia, gera automaticamente.
	 * @return array { created, skipped, errors[] }
	 */
	public static function import_csv( $file_path, $default_loc = 0, $send_email = false, $default_password = '' ) {
		$result = [ 'created' => 0, 'skipped' => 0, 'errors' => [] ];

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			$result['errors'][] = 'Não foi possível abrir o arquivo.';
			return $result;
		}

		// Detecta separador: lê primeira linha
		$first = fgets( $handle );
		rewind( $handle );
		$sep = ( substr_count( $first, ';' ) >= substr_count( $first, ',' ) ) ? ';' : ',';

		// Remove BOM UTF-8 se presente
		$bom = "\xEF\xBB\xBF";
		$row = fgetcsv( $handle, 0, $sep );
		if ( $row && strpos( $row[0], $bom ) === 0 ) {
			$row[0] = substr( $row[0], 3 );
		}
		// Normaliza cabeçalho para detectar colunas
		$header = array_map( function( $h ) {
			return mb_strtoupper( trim( $h ), 'UTF-8' );
		}, $row );

		// Mapeia índices das colunas
		$col_nome      = array_search( 'NOME',               $header );
		$col_email     = array_search( 'E-MAIL',             $header );
		if ( $col_email === false )  $col_email  = array_search( 'EMAIL',     $header );
		$col_unidade   = array_search( 'UNIDADE',            $header );
		$col_funcao    = array_search( 'FUNÇÃO',             $header );
		if ( $col_funcao === false ) $col_funcao = array_search( 'FUNCAO',    $header );
		$col_admissao  = array_search( 'DATA DE ADMISSÃO',   $header );
		if ( $col_admissao === false )    $col_admissao    = array_search( 'ADMISSÃO',   $header );
		if ( $col_admissao === false )    $col_admissao    = array_search( 'ADMISSAO',   $header );
		if ( $col_admissao === false )    $col_admissao    = array_search( 'HIRE DATE',  $header );
		$col_aniversario = array_search( 'DATA DE ANIVERSÁRIO', $header );
		if ( $col_aniversario === false ) $col_aniversario = array_search( 'ANIVERSÁRIO',  $header );
		if ( $col_aniversario === false ) $col_aniversario = array_search( 'ANIVERSARIO',  $header );
		if ( $col_aniversario === false ) $col_aniversario = array_search( 'BIRTH DATE',   $header );

		if ( $col_nome === false || $col_email === false ) {
			$result['errors'][] = 'O arquivo precisa ter as colunas NOME e E-MAIL.';
			fclose( $handle );
			return $result;
		}

		// Cache de unidades por nome (lower-case) para resolução rápida
		$location_cache = [];
		foreach ( KT_Location::get_all() as $loc ) {
			$location_cache[ mb_strtolower( trim( $loc->name ), 'UTF-8' ) ] = (int) $loc->id;
		}

		$line = 1;
		while ( ( $row = fgetcsv( $handle, 0, $sep ) ) !== false ) {
			$line++;
			if ( count( $row ) < 2 ) continue;

			$nome        = trim( $row[ $col_nome ]  ?? '' );
			$email       = sanitize_email( trim( $row[ $col_email ] ?? '' ) );
			$unidade     = $col_unidade    !== false ? trim( $row[ $col_unidade ]    ?? '' ) : '';
			$funcao      = $col_funcao     !== false ? trim( $row[ $col_funcao ]     ?? '' ) : '';
			$admissao    = $col_admissao   !== false ? trim( $row[ $col_admissao ]   ?? '' ) : '';
			$aniversario = $col_aniversario !== false ? trim( $row[ $col_aniversario ] ?? '' ) : '';

			// Normaliza datas: aceita DD/MM/AAAA ou AAAA-MM-DD → sempre salva AAAA-MM-DD
			$admissao    = self::normalize_date( $admissao );
			$aniversario = self::normalize_date( $aniversario );

			if ( empty( $nome ) || empty( $email ) ) {
				$result['errors'][] = "Linha $line: NOME e E-MAIL são obrigatórios — pulada.";
				continue;
			}
			if ( ! is_email( $email ) ) {
				$result['errors'][] = "Linha $line: e-mail inválido ($email) — pulada.";
				continue;
			}

			// Resolve unidade
			$location_id = (int) $default_loc;
			if ( $unidade ) {
				$key = mb_strtolower( $unidade, 'UTF-8' );
				if ( isset( $location_cache[ $key ] ) ) {
					$location_id = $location_cache[ $key ];
				} else {
					$result['errors'][] = "Linha $line: unidade \"$unidade\" não encontrada — colaborador criado sem unidade.";
				}
			}

			// Split nome em first/last (necessário antes de gerar o username)
			$name_parts = explode( ' ', trim( $nome ) );
			$first_name = $name_parts[0];
			$last_name  = count( $name_parts ) > 1 ? $name_parts[ count( $name_parts ) - 1 ] : '';

			// Gera username no formato primeironome.ultimosobrenome
			$base_user = sanitize_user( mb_strtolower(
				$first_name . ( $last_name ? '.' . $last_name : '' ),
				'UTF-8'
			) );
			$username  = $base_user;
			$suffix    = 1;
			while ( username_exists( $username ) || email_exists( $email ) ) {
				// Se o e-mail já existe, apenas vincula se não for membro ainda
				if ( email_exists( $email ) ) {
					$existing = get_user_by( 'email', $email );
					if ( $existing ) {
						global $wpdb;
						$already = $wpdb->get_var( $wpdb->prepare(
							"SELECT id FROM {$wpdb->prefix}kt_members WHERE user_id = %d", $existing->ID
						) );
						if ( $already ) {
							$result['skipped']++;
						} else {
							// Vincula usuário existente
							$r = self::create( [
								'existing_user_id' => $existing->ID,
								'location_id'      => $location_id,
								'position_id'      => $position_id,
							] );
							if ( is_wp_error( $r ) ) {
								$result['errors'][] = "Linha $line ($email): " . $r->get_error_message();
							} else {
								$result['created']++;
							}
						}
					}
					continue 2; // pula para a próxima linha do CSV
				}
				$username = $base_user . $suffix;
				$suffix++;
			}

			// Resolve position_id pelo nome da função
			$position_id = null;
			if ( $funcao ) {
				foreach ( KT_Position::get_all() as $pos ) {
					if ( mb_strtolower( $pos->name, 'UTF-8' ) === mb_strtolower( $funcao, 'UTF-8' ) ) {
						$position_id = (int) $pos->id;
						break;
					}
				}
				if ( ! $position_id ) {
					$result['errors'][] = "Linha $line: função \"$funcao\" não encontrada — colaborador criado sem função. Crie a função em Keen Training → Funções.";
				}
			}

			// Senha: usa a padrão definida pelo admin, ou aplica o padrão da empresa
			$temp_pass = ! empty( $default_password )
				? $default_password
				: 'fazerbemfeito';

			$r = self::create( [
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'email'       => $email,
				'username'    => $username,
				'password'    => $temp_pass,
				'location_id' => $location_id,
				'position_id' => $position_id,
				'hire_date'   => $admissao,
				'birth_date'  => $aniversario,
			] );

			if ( is_wp_error( $r ) ) {
				$result['errors'][] = "Linha $line ($email): " . $r->get_error_message();
			} else {
				$result['created']++;
				// Envia e-mail de boas-vindas com link de acesso (sem expor a senha)
				if ( $send_email ) {
					$new_user = get_user_by( 'email', $email );
					if ( $new_user ) {
						wp_new_user_notification( $new_user->ID, null, 'user' );
					}
				}
			}
		}

		fclose( $handle );
		return $result;
	}

	/**
	 * Normaliza uma string de data para o formato AAAA-MM-DD.
	 * Aceita: AAAA-MM-DD (ISO), DD/MM/AAAA, DD-MM-AAAA.
	 * Retorna '' se a data for vazia ou inválida.
	 */
	private static function normalize_date( $value ) {
		$value = trim( $value );
		if ( $value === '' ) return '';

		// Já está em ISO: AAAA-MM-DD
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return checkdate( (int) substr( $value, 5, 2 ), (int) substr( $value, 8, 2 ), (int) substr( $value, 0, 4 ) )
				? $value : '';
		}

		// DD/MM/AAAA ou DD-MM-AAAA
		if ( preg_match( '/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $m ) ) {
			[ , $d, $mo, $y ] = $m;
			return checkdate( (int) $mo, (int) $d, (int) $y )
				? sprintf( '%04d-%02d-%02d', $y, $mo, $d ) : '';
		}

		return '';
	}
}
