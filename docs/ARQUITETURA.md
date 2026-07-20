# Arquitetura e regras do Notes System

Este documento complementa os comentários do código. Ele descreve responsabilidades e regras de negócio sem repetir a sintaxe do framework.

## Fluxo de uma requisição

1. `routes/web.php` associa a URL ao controlador e aplica os middlewares necessários.
2. O middleware valida a sessão, a atividade da conta e, quando necessário, a função de administrador.
3. O controlador valida a entrada e limita consultas ao usuário autenticado.
4. Os modelos Eloquent leem ou persistem os dados.
5. `ActivityLogger` registra movimentações relevantes sem interromper a ação principal em caso de falha de auditoria.
6. Uma view Blade renderiza a resposta usando o layout compartilhado.

## Módulos

### Autenticação e contas

- `LoginController` concentra login, cadastro, recuperação, logout e criação da sessão.
- `NoteUser` é a conta efetivamente usada pelo sistema.
- `StrongPassword` mantém uma única política para cadastro e redefinição.
- `EnsureNoteUserAuthenticated` bloqueia sessões ausentes ou contas que foram desativadas.
- `EnsureAdministrator` restringe a gestão de usuários a contas administrativas.
- `AdminUserController` cria contas, altera nome, função e estado, e redefine senhas.

Regra principal: uma conta inativa não pode acessar recursos privados. O último administrador ativo não deve ser rebaixado ou bloqueado por engano.

### Notas

- `NoteController` lista, cria, exibe, atualiza e envia notas para a Lixeira.
- `Note` armazena conteúdo, status, prioridade, etiquetas, categoria e exclusão lógica.
- `notes/editor.blade.php` é reutilizado pela criação e pela edição.
- `notes/index.blade.php` exibe indicadores, cards e o modal de visualização.

Status persistidos:

- `em_andamento`: trabalho ativo;
- `pendente`: item aguardando ação;
- `concluida`: nota finalizada e contabilizada nos indicadores.

Regra principal: toda consulta mutável localiza a nota pelo identificador e pelo `user_name` da sessão, evitando acesso entre usuários.

### Categorias

- `CategoryController` calcula indicadores e mantém categorias.
- `Category` relaciona uma categoria a várias notas.
- `layout/categories.blade.php` reúne cadastro, edição, busca e tabela.

Uma categoria inativa deixa de aparecer como opção para novas edições, mas continua preservada. Ao remover uma categoria, a chave estrangeira usa `nullOnDelete`, portanto as notas continuam existentes e passam a ficar sem categoria.

### Lixeira

- `TrashController` lista registros com exclusão lógica, restaura, exclui um item definitivamente ou esvazia a Lixeira.
- `notes/trash.blade.php` contém confirmações para ações irreversíveis.

Regra principal: `delete` na nota ativa é reversível; `forceDelete` na Lixeira é definitivo e sempre exige confirmação na interface.

### Painel e atividades

- `ActivityLogger` grava ações vinculadas ao usuário.
- `Activity` representa cada movimentação.
- `PanelController` consolida métricas, séries temporais e filtros.
- `panel/index.blade.php` permite escolher período, dado e formato do gráfico.
- `components/panel/stat-card.blade.php` mantém os indicadores reutilizáveis.

As séries são preenchidas dia a dia, inclusive com zero, para manter os gráficos de 7, 30 e 90 dias alinhados. A migração de backfill cria eventos históricos de notas e categorias já existentes.

## Modelo de dados

| Tabela | Finalidade | Vínculos importantes |
| --- | --- | --- |
| `note_users` | Contas, função, estado e recuperação | `user_name` identifica o proprietário dos dados. |
| `notes` | Conteúdo e organização das notas | Categoria opcional e `deleted_at` para Lixeira. |
| `categories` | Agrupamentos personalizados | Pertence logicamente a um `user_name`. |
| `activities` | Histórico usado pelo Painel | Pode referenciar o tipo e o id do objeto alterado. |
| `sections` | Divisões antigas mantidas por compatibilidade | Pertence a uma nota e é removida em cascata. |

As migrações antigas não devem ser alteradas depois de aplicadas. Mudanças futuras devem entrar em um novo arquivo de migração com operações `up` e `down` documentadas.

## Interface

- `layout/app.blade.php` fornece cabeçalho, menu lateral, busca e modais globais.
- `layout/sidebar.blade.php` exibe links conforme a função da conta.
- `layout/blank.blade.php` atende páginas sem navegação principal.
- `login`, `register`, `recover` e `recover_answer` implementam o fluxo público.
- `resources/css/style.css` organiza estilos por seções comentadas e preserva a identidade laranja.
- `resources/js/app.js` contém apenas o comportamento global dos cards; scripts específicos permanecem próximos às suas views.

`password.blade.php` e as telas de `section_*` são artefatos legados mantidos para compatibilidade. O fluxo atual de login envia nome e senha juntos, e o conteúdo principal da nota substituiu as antigas divisões.

## PWA

- `public/manifest.json` define nome, cores e ícones instaláveis.
- `public/sw.js` mantém um cache mínimo dos arquivos de abertura.
- `public/icons` contém os ícones e o utilitário usado para gerá-los.

O service worker não armazena respostas privadas de notas; pedidos fora da lista mínima continuam seguindo para a rede.

## Produção

- `Dockerfile` monta a imagem PHP/Apache, instala dependências e gera o frontend.
- `docker-entrypoint.sh` executa migrações antes do servidor.
- `render.yaml` descreve o serviço, sem armazenar segredos.
- `AppServiceProvider` força HTTPS quando `APP_URL` usa esse esquema.

Antes de publicar, executar `php artisan test` e `npm run build`, confirmar `APP_DEBUG=false`, configurar uma `APP_KEY` exclusiva e disponibilizar armazenamento persistente para os dados necessários.

## Convenção de documentação

- comentários de classe ou método explicam responsabilidade ou regra, não a sintaxe;
- comentários Blade identificam regiões funcionais e condições de segurança;
- migrações documentam o efeito de avanço e reversão;
- arquivos gerados pelo Laravel, dependências e artefatos de build não recebem comentários locais.
