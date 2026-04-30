/* Keen Training — Portal do Colaborador JS */
(function ($) {
	'use strict';

	if (typeof ktFrontend === 'undefined') return;

	/* -----------------------------------------------------------------------
	 * Highlight selected answer — radio (single) and checkbox (multiple_select)
	 * -------------------------------------------------------------------- */
	$(document).on('change', '.kt-response-input', function () {
		var $input = $(this);
		var qid    = $input.data('question-id');

		if ($input.is('[type="radio"]')) {
			// Radio: deselect all in group, select this one
			$('[data-question-id="' + qid + '"]').closest('.kt-answer-option').removeClass('selected');
			$input.closest('.kt-answer-option').addClass('selected');
		} else {
			// Checkbox: toggle
			$input.closest('.kt-answer-option').toggleClass('selected', $input.is(':checked'));
		}

		// Update progress bar based on answered questions
		updateProgress();

		// Remove unanswered indicator
		$input.closest('.kt-question').removeClass('kt-unanswered');
		$input.closest('.kt-question').find('.kt-unanswered-msg').remove();
	});

	/* -----------------------------------------------------------------------
	 * Progress bar updater
	 * -------------------------------------------------------------------- */
	function updateProgress() {
		var total    = $('.kt-quiz .kt-question').length;
		if (!total) return;
		var answered = 0;
		$('.kt-quiz .kt-question').each(function () {
			var qid      = $(this).data('question-id');
			var $radios  = $(this).find('input[type="radio"]:checked');
			var $checks  = $(this).find('input[type="checkbox"]:checked');
			if ($radios.length > 0 || $checks.length > 0) answered++;
		});
		var pct    = Math.round((answered / total) * 100);
		var label  = 'Pergunta ' + Math.min(answered + 1, total) + ' de ' + total;
		if (answered === total) label = 'Todas as perguntas respondidas';
		$('#kt-progress-fill').css('width', pct + '%');
		$('#kt-progress-label').text(label);
	}

	/* -----------------------------------------------------------------------
	 * Marcar módulo como concluído
	 * -------------------------------------------------------------------- */
	$(document).on('click', '.kt-complete-module', function () {
		var $btn     = $(this);
		var moduleId = $btn.data('module-id');
		var nextUrl  = $btn.data('next-url') || null;

		$btn.prop('disabled', true).text(ktFrontend.i18n.salvando);

		$.post(ktFrontend.ajaxUrl, {
			action:    'kt_complete_module',
			nonce:     ktFrontend.nonce,
			module_id: moduleId
		})
		.done(function (resp) {
			if (resp.success) {
				var pct = resp.data.progress;

				var $item = $btn.closest('.kt-module-item');
				$item.addClass('kt-module-complete');
				$btn.replaceWith('<span class="kt-module-done-label">✓ ' + ktFrontend.i18n.concluido + '</span>');
				$item.find('.kt-module-number').css({background: '#22c55e', color: '#fff'}).text('✓');
				$item.find('.kt-module-status-badge').html('<span class="kt-badge kt-badge-success">Concluído</span>');

				$('#kt-course-progress-bar').css('width', pct + '%');
				$('#kt-course-progress-label').text(pct + '% concluído');

				if (nextUrl) {
					window.location.href = nextUrl;
					return;
				}

				if (resp.data.course_done || pct >= 100) {
					$('#kt-completion-banner').show();
					$('html, body').animate({ scrollTop: $('#kt-completion-banner').offset().top - 80 }, 500);
				}
			} else {
				$btn.prop('disabled', false).text('✔ Marcar como Concluído');
				var msg = resp.data && resp.data.message ? resp.data.message : ktFrontend.i18n.erro_rede;
				showAlert(msg, 'fail');
			}
		})
		.fail(function () {
			$btn.prop('disabled', false).text('✔ Marcar como Concluído');
			showAlert(ktFrontend.i18n.erro_rede, 'fail');
		});
	});

	/* -----------------------------------------------------------------------
	 * Collect responses from a quiz form (handles radio + checkbox)
	 * -------------------------------------------------------------------- */
	function collectResponses($form) {
		var responses = {};

		// Radio inputs
		$form.find('input[type="radio"].kt-response-input:checked').each(function () {
			responses[$(this).data('question-id')] = $(this).val();
		});

		// Checkbox inputs (multiple_select) — build arrays
		$form.find('input[type="checkbox"].kt-response-input').each(function () {
			var qid = $(this).data('question-id');
			if (!responses[qid]) responses[qid] = [];
			if ($(this).is(':checked')) {
				if (!Array.isArray(responses[qid])) responses[qid] = [];
				responses[qid].push($(this).val());
			} else {
				if (!Array.isArray(responses[qid])) responses[qid] = [];
			}
		});

		return responses;
	}

	/* -----------------------------------------------------------------------
	 * Validate that every question has at least one selection
	 * -------------------------------------------------------------------- */
	function validateAllAnswered($form) {
		var allAnswered = true;
		$form.find('.kt-question').each(function () {
			var $q      = $(this);
			var qid     = $q.data('question-id');
			var checked = $q.find('.kt-response-input:checked').length;
			if (!checked) {
				allAnswered = false;
				$q.addClass('kt-unanswered');
				if (!$q.find('.kt-unanswered-msg').length) {
					$q.find('.kt-answers').before('<p class="kt-unanswered-msg" style="color:#b91c1c;font-size:.85em;margin:4px 0">⚠ Responda esta pergunta.</p>');
				}
			} else {
				$q.removeClass('kt-unanswered');
				$q.find('.kt-unanswered-msg').remove();
			}
		});
		return allAnswered;
	}

	/* -----------------------------------------------------------------------
	 * renderReview — build HTML review section from snapshot
	 * startOpen: true = expanded by default (used on fail)
	 * -------------------------------------------------------------------- */
	function renderReview(snapshot, startOpen) {
		if (!snapshot || !snapshot.length) return '';

		var btnLabel  = startOpen ? 'Ocultar revisão ▲' : 'Ver revisão das respostas ▼';
		var bodyStyle = startOpen ? '' : 'display:none';

		var html = '<div class="kt-review-section">'
			+ '<button type="button" class="kt-btn kt-btn-sm kt-review-toggle" style="margin-bottom:12px">' + btnLabel + '</button>'
			+ '<div class="kt-review-body" style="' + bodyStyle + '">';

		for (var i = 0; i < snapshot.length; i++) {
			var q = snapshot[i];
			html += '<div class="kt-review-question ' + (q.is_correct ? 'kt-review-correct' : 'kt-review-wrong') + '">';
			html += '<p class="kt-review-q-text"><strong>' + (i + 1) + '.</strong> ' + escHtml(q.question_text) + '</p>';
			html += '<ul class="kt-review-answers">';

			for (var j = 0; j < q.answers.length; j++) {
				var ans        = q.answers[j];
				var userPicked = q.user_answer_ids.indexOf(ans.id) !== -1;
				var isCor      = ans.is_correct;
				var cls        = '';
				if (isCor && userPicked)       cls = 'kt-review-answer correct';
				else if (!isCor && userPicked) cls = 'kt-review-answer wrong-selected';
				else if (isCor && !userPicked) cls = 'kt-review-answer correct-not-selected';
				else                           cls = 'kt-review-answer';

				var icon = '';
				if (isCor && userPicked)       icon = '✓ ';
				else if (!isCor && userPicked) icon = '✗ ';
				else if (isCor && !userPicked) icon = '○ ';

				html += '<li class="' + cls + '">' + icon + escHtml(ans.text) + '</li>';
			}
			html += '</ul>';

			if (q.explanation) {
				html += '<div class="kt-explanation"><strong>Explicação:</strong> ' + escHtml(q.explanation) + '</div>';
			}
			html += '</div>';
		}

		html += '</div></div>';
		return html;
	}

	function escHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	/* -----------------------------------------------------------------------
	 * Submeter avaliação (portal view)
	 * -------------------------------------------------------------------- */
	$(document).on('submit', '#kt-quiz-form', function (e) {
		e.preventDefault();

		var $form    = $(this);
		var quizId   = $('#kt-quiz-id').val();
		var moduleId = $('#kt-module-id').val();
		var responses = collectResponses($form);

		// Collect question_ids[] hidden inputs
		var questionIds = [];
		$form.find('input[name="question_ids[]"]').each(function () {
			questionIds.push($(this).val());
		});

		if (!validateAllAnswered($form)) {
			$('html, body').animate({ scrollTop: $form.find('.kt-unanswered').first().offset().top - 100 }, 400);
			return;
		}

		var $btn = $('#kt-quiz-submit');
		$btn.prop('disabled', true).text(ktFrontend.i18n.enviando);

		$.post(ktFrontend.ajaxUrl, {
			action:       'kt_submit_quiz',
			nonce:        ktFrontend.nonce,
			quiz_id:      quizId,
			module_id:    moduleId,
			responses:    responses,
			question_ids: questionIds
		})
		.done(function (resp) {
			$btn.prop('disabled', false).text('Enviar Avaliação');
			var $result = $('#kt-quiz-result');

			if (resp.success) {
				var d = resp.data;
				var nextUrl   = $('#kt-next-mod-url').val() || '';
				var courseUrl = $('#kt-course-url').val()   || '';

				// Sempre esconde o formulário — o resultado substitui a tela
				$form.hide();

				var scoreHtml = '<div class="kt-score-display">' + d.score + '%</div>';
				var html = scoreHtml
					+ '<p class="kt-result-message">' + escHtml(d.message) + '</p>'
					+ '<p>Acertos: <strong>' + d.correct + ' de ' + d.total + '</strong></p>';

				var actionHtml = '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">';
				if (d.passed) {
					// Botões de navegação pós-aprovação
					if (nextUrl) {
						actionHtml += '<a href="' + escHtml(nextUrl) + '" class="kt-btn kt-btn-primary">Próximo Módulo →</a>';
					}
					if (courseUrl) {
						actionHtml += '<a href="' + escHtml(courseUrl) + '" class="kt-btn kt-btn-outline">← Voltar ao Curso</a>';
					}
				} else {
					var ilimitado = (d.tentativas_restantes === -1);
					if (ilimitado || d.tentativas_restantes > 0) {
						if (!ilimitado) {
							actionHtml += '<span style="align-self:center;font-size:.88em;color:#64748b">Tentativas restantes: <strong>' + d.tentativas_restantes + '</strong></span>';
						}
						actionHtml += '<button type="button" class="kt-btn kt-btn-primary" id="kt-retry-btn">↺ Refazer Avaliação</button>';
					} else {
						actionHtml += '<span style="color:#64748b;font-size:.9em">Sem tentativas restantes. Fale com seu gerente.</span>';
					}
					if (courseUrl) {
						actionHtml += '<a href="' + escHtml(courseUrl) + '" class="kt-btn kt-btn-outline">← Voltar ao Curso</a>';
					}
				}
				actionHtml += '</div>';
				html += actionHtml;

				// Revisão: aberta por padrão na reprova, colapsada na aprovação
				if (d.snapshot && d.snapshot.length) {
					html += renderReview(d.snapshot, !d.passed);
				}

				$result
					.removeClass('kt-result-pass kt-result-fail')
					.addClass(d.passed ? 'kt-result-pass' : 'kt-result-fail')
					.html(html)
					.show();

				$('html, body').animate({ scrollTop: $result.offset().top - 80 }, 400);
			} else {
				var msg = resp.data && resp.data.message ? resp.data.message : ktFrontend.i18n.erro_rede;
				$result.removeClass('kt-result-pass kt-result-fail')
					.addClass('kt-result-fail').html('<p>' + escHtml(msg) + '</p>').show();
			}
		})
		.fail(function () {
			$btn.prop('disabled', false).text('Enviar Avaliação');
			showAlert(ktFrontend.i18n.erro_rede, 'fail');
		});
	});

	/* -----------------------------------------------------------------------
	 * Shortcode [kt_quiz] — formulário embed em páginas Elementor
	 * -------------------------------------------------------------------- */
	$(document).on('submit', '.kt-quiz-embed-form', function (e) {
		e.preventDefault();

		var $form    = $(this);
		var quizId   = $form.find('.kt-quiz-id-input').val();
		var moduleId = $form.find('.kt-module-id-input').val();
		var $result  = $('#kt-quiz-result-' + quizId);
		var $submit  = $form.find('.kt-quiz-embed-submit');
		var responses = collectResponses($form);

		// Collect question_ids[] hidden inputs
		var questionIds = [];
		$form.find('input[name="question_ids[]"]').each(function () {
			questionIds.push($(this).val());
		});

		if (!validateAllAnswered($form)) {
			$('html, body').animate({ scrollTop: $form.find('.kt-unanswered').first().offset().top - 100 }, 400);
			return;
		}

		$submit.prop('disabled', true).text(ktFrontend.i18n.enviando);

		$.post(ktFrontend.ajaxUrl, {
			action:       'kt_submit_quiz',
			nonce:        ktFrontend.nonce,
			quiz_id:      quizId,
			module_id:    moduleId,
			responses:    responses,
			question_ids: questionIds
		})
		.done(function (resp) {
			$submit.prop('disabled', false).text('Enviar Avaliação');
			if (resp.success) {
				var d = resp.data;

				// Sempre esconde o formulário
				$form.slideUp(200);

				var scoreHtml = '<div class="kt-score-display">' + d.score + '%</div>';
				var html = scoreHtml
					+ '<p class="kt-result-message">' + escHtml(d.message) + '</p>'
					+ '<p>Acertos: <strong>' + d.correct + ' de ' + d.total + '</strong></p>';

				var actionHtml = '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">';
				if (!d.passed) {
					var ilimitado = (d.tentativas_restantes === -1);
					if (ilimitado || d.tentativas_restantes > 0) {
						if (!ilimitado) {
							actionHtml += '<span style="align-self:center;font-size:.88em;color:#64748b">Tentativas restantes: <strong>' + d.tentativas_restantes + '</strong></span>';
						}
						actionHtml += '<button type="button" class="kt-btn kt-btn-primary kt-embed-retry-btn" data-quiz-id="' + quizId + '">↺ Refazer Avaliação</button>';
					} else {
						actionHtml += '<span style="color:#64748b;font-size:.9em">Sem tentativas restantes. Fale com seu gerente.</span>';
					}
				}
				actionHtml += '</div>';
				html += actionHtml;

				// Revisão: aberta por padrão na reprova
				if (d.snapshot && d.snapshot.length) {
					html += renderReview(d.snapshot, !d.passed);
				}

				$result
					.removeClass('kt-result-pass kt-result-fail')
					.addClass(d.passed ? 'kt-result-pass' : 'kt-result-fail')
					.html(html).show();

				$('html, body').animate({ scrollTop: $result.offset().top - 80 }, 400);
			} else {
				var msg = resp.data && resp.data.message ? resp.data.message : ktFrontend.i18n.erro_rede;
				$result.removeClass('kt-result-pass kt-result-fail')
					.addClass('kt-result-fail').html('<p>' + escHtml(msg) + '</p>').show();
			}
		})
		.fail(function () {
			$submit.prop('disabled', false).text('Enviar Avaliação');
			$result.addClass('kt-result-fail').html('<p>' + ktFrontend.i18n.erro_rede + '</p>').show();
		});
	});

	/* Toggle review section */
	$(document).on('click', '.kt-review-toggle', function () {
		var $body = $(this).siblings('.kt-review-body');
		var open  = $body.is(':visible');
		$body.slideToggle(200);
		$(this).text(open ? 'Ver revisão das respostas ▼' : 'Ocultar revisão ▲');
	});

	/* Botão tentar novamente (embed) */
	$(document).on('click', '.kt-embed-retry-btn', function () {
		var $embed = $(this).closest('.kt-quiz-embed');
		var quizId = $(this).data('quiz-id');
		$('#kt-quiz-result-' + quizId).hide();
		$embed.find('.kt-quiz-embed-form').show();
		$embed.find('.kt-answer-option').removeClass('selected');
		$embed.find('.kt-response-input').prop('checked', false);
		$embed.find('.kt-unanswered').removeClass('kt-unanswered');
		$embed.find('.kt-unanswered-msg').remove();
		$('html, body').animate({ scrollTop: $embed.offset().top - 80 }, 400);
	});

	/* Botão refazer avaliação (portal) */
	$(document).on('click', '#kt-retry-btn', function () {
		$('#kt-quiz-result').hide().removeClass('kt-result-pass kt-result-fail');
		var $form = $('#kt-quiz-form');
		$form.find('.kt-answer-option').removeClass('selected');
		$form.find('.kt-response-input').prop('checked', false);
		$form.find('.kt-unanswered').removeClass('kt-unanswered');
		$form.find('.kt-unanswered-msg').remove();
		$form.show();
		updateProgress();
		$('html, body').animate({ scrollTop: $form.offset().top - 80 }, 400);
	});

	/* Helper: alerta embutido */
	function showAlert(msg, type) {
		var cls   = type === 'fail' ? 'kt-result-fail' : 'kt-result-pass';
		var $alert = $('<div class="kt-quiz-result ' + cls + '" style="margin:16px 0"><p>' + escHtml(msg) + '</p></div>');
		$('.kt-module-actions').first().prepend($alert);
		setTimeout(function () { $alert.fadeOut(function () { $alert.remove(); }); }, 5000);
	}

})(jQuery);
