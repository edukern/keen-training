# Keen Training — Contexto do Projeto

Plugin WordPress de treinamento corporativo para rede de lojas de roupas.
Repositório: https://github.com/edukern/keen-training

## Versão atual
2.3.3

## Estrutura principal
- `keen-training.php` — bootstrap, versão, autoload de classes, cron
- `includes/` — classes PHP: Installer, Member, Course, Quiz, Progress, Certificate, Notifications, Roles, Location, Position, Restriction
- `admin/` — painel administrativo (class-admin.php + views/)
- `frontend/` — portal do colaborador e gerente (class-frontend.php + views/)
- `assets/` — admin.css, admin.js, frontend.css, frontend.js, modelo-colaboradores.csv

## Como publicar uma atualização
1. Incrementar `KT_VERSION` em `keen-training.php` (header + define)
2. Adicionar bloco de migração em `includes/class-installer.php` → `maybe_upgrade()` se houver mudança de banco
3. `git add` nos arquivos alterados → `git commit` → `git push origin main`
4. `git archive --format=zip --prefix=keen-training/ HEAD -o keen-training-X.Y.Z.zip`
5. `gh release create vX.Y.Z keen-training-X.Y.Z.zip --title "..." --notes "..."`

## Convenções
- Prefixo de tabelas: `kt_` (ex: `wp_kt_members`)
- Prefixo de opções WP: `kt_` (ex: `kt_primary_color`)
- Prefixo de hooks/actions: `kt_`
- Idioma: português brasileiro em todas as interfaces
- Datas salvas em formato `YYYY-MM-DD` no banco
- Commits em inglês, interfaces em PT-BR

## Unidades cadastradas no sistema
Nova Hartz, Três Coroas, Igrejinha, Osório, CD

## Observações importantes
- Não usar biblioteca PDF externa — certificados usam window.print() com HTML
- Todas as queries usam $wpdb->prepare()
- Verificação de nonce em todos os formulários
- Capacidade `can_manage_location()` controla o que gerentes de unidade enxergam
