<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap kt-wrap">
	<h1>Certificados Emitidos</h1>
	<p class="description">Certificados são gerados automaticamente quando um colaborador conclui todos os módulos de um curso.</p>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Colaborador</th>
				<th>Unidade</th>
				<th>Curso</th>
				<th>Data de Emissão</th>
				<th>Ação</th>
			</tr>
		</thead>
		<tbody>
		<?php if ( ! $certs ): ?>
			<tr><td colspan="5" style="text-align:center;padding:24px;color:#888">Nenhum certificado emitido ainda.</td></tr>
		<?php else: ?>
		<?php foreach ( $certs as $cert ): ?>
			<tr>
				<td><?php echo esc_html( $cert->full_name ?: $cert->display_name ); ?></td>
				<td><?php echo esc_html( $cert->location_name ?? '—' ); ?></td>
				<td><?php echo esc_html( $cert->course_title ); ?></td>
				<td><?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $cert->issued_at ) ) ); ?></td>
				<td>
					<a href="<?php echo esc_url( add_query_arg( [ 'kt_cert' => $cert->cert_uid ], home_url( '/' ) ) ); ?>" target="_blank" class="button button-small">🏆 Ver / Imprimir</a>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
