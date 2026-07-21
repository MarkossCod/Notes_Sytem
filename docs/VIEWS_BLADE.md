# Guia das views Blade

Este guia foi escrito para quem possui noções básicas de HTML, PHP e JavaScript e precisa alterar as telas do Notes System com segurança.

## O que é uma view Blade

Blade é o sistema de templates do Laravel. Um arquivo `.blade.php` mistura HTML com comandos simples que recebem dados do backend. A view deve cuidar da apresentação; consultas ao banco, cálculos e autorização pertencem aos controladores, modelos e middlewares.

Fluxo básico:

1. uma rota em `routes/web.php` chama um método do controlador;
2. o controlador consulta e valida os dados;
3. o controlador retorna `view('pasta.arquivo', [...])`;
4. a view usa as variáveis recebidas para montar o HTML;
5. CSS define a aparência e JavaScript controla interações locais.

## Sintaxe essencial

| Sintaxe | Finalidade |
| --- | --- |
| `{{ $valor }}` | Exibe texto escapando HTML potencialmente perigoso. |
| `@if`, `@else`, `@endif` | Exibe um trecho de acordo com uma condição. |
| `@foreach`, `@endforeach` | Repete um trecho para cada item de uma lista. |
| `@extends('layout.app')` | Usa um layout como estrutura principal. |
| `@section('content')` | Define o conteúdo que será inserido pelo layout. |
| `@include('notes.editor')` | Inclui outra view no ponto atual. |
| `<x-panel.stat-card />` | Renderiza um componente Blade reutilizável. |
| `@csrf` | Adiciona a proteção obrigatória aos formulários. |
| `@method('PUT')` | Informa ao Laravel que um formulário POST representa PUT, PATCH ou DELETE. |
| `route('nome')` | Gera a URL por meio do nome da rota, evitando endereços fixos. |

Prefira `{{ $valor }}`. Não use saída sem escape para conteúdo digitado por usuários.

## Onde alterar cada tela

| Tela ou elemento | Arquivo principal | Dados preparados por |
| --- | --- | --- |
| Estrutura interna | `layout/app.blade.php` | Sessão e página atual |
| Menu lateral | `layout/sidebar.blade.php` | Sessão e middlewares |
| Página inicial | `notes/index.blade.php` | `NoteController@index` |
| Criar nota | `notes/create.blade.php` + `notes/editor.blade.php` | `NoteController@create` |
| Editar nota | `notes/show.blade.php` + `notes/editor.blade.php` | `NoteController@show` |
| Categorias | `layout/categories.blade.php` | `CategoryController@index` |
| Lixeira | `notes/trash.blade.php` | `TrashController@index` |
| Painel | `panel/index.blade.php` | `PanelController@index` |
| Usuários | `admin/users/index.blade.php` | `AdminUserController@index` |
| Login e cadastro | `login.blade.php` e `register.blade.php` | `LoginController` |
| Recuperação | `recover*.blade.php` | `LoginController` |

Os arquivos `notes/section_create.blade.php`, `notes/section_edit.blade.php` e `password.blade.php` são legados e não participam das rotas atuais.

## Como fazer uma alteração visual

Exemplo: mudar um texto, reorganizar um bloco ou adicionar uma classe.

1. localize a view pela tabela acima;
2. leia o cabeçalho de manutenção no início do arquivo;
3. altere o HTML ou as classes sem mudar `name`, `id`, `data-*`, rota ou método do formulário;
4. procure a classe em `resources/css/style.css` e ajuste o estilo necessário;
5. verifique a página em largura normal e em tela pequena;
6. execute as validações descritas no fim deste guia.

## Como exibir um novo dado

Exemplo: mostrar uma nova contagem no Painel.

1. calcule o valor no controlador correspondente;
2. adicione o valor ao array enviado para a view;
3. exiba com `{{ $variavel }}` ou passe como propriedade a um componente;
4. adicione um teste que confirme o cálculo e a exibição.

Evite realizar consultas Eloquent dentro da view. Isso dificulta testes, pode repetir consultas e mistura regra de negócio com apresentação.

## Formulários e segurança

Ao alterar um formulário:

- mantenha `@csrf` em POST, PUT, PATCH e DELETE;
- mantenha `@method(...)` quando a rota não for POST;
- confirme que cada `name` corresponde ao campo validado no controlador;
- use `route('nome')` em vez de escrever uma URL fixa;
- não confie apenas em campos ocultos ou JavaScript para autorização;
- ações irreversíveis devem continuar exigindo confirmação;
- nunca exiba senha, hash ou resposta secreta.

## Relação entre HTML e JavaScript

Funções JavaScript localizam elementos por `id`, classe ou atributo `data-*`. Antes de renomear um desses valores, pesquise todas as ocorrências no mesmo arquivo e em `resources/js`.

Exemplo:

```html
<button id="neDeleteBtn">Excluir</button>
```

Se o ID mudar, `document.getElementById('neDeleteBtn')` também precisa mudar. Caso contrário, o botão deixará de funcionar sem necessariamente apresentar um erro visível.

## Componentes reutilizáveis

Os componentes em `resources/views/components` recebem propriedades. Ao adicionar uma propriedade obrigatória:

1. declare-a com `@props` quando o componente utilizar essa diretiva;
2. atualize todas as chamadas `<x-...>`;
3. defina um valor padrão quando a informação puder ser omitida;
4. mantenha estilos por tonalidade no CSS.

## Validação antes de concluir

No diretório do projeto, execute:

```bash
php artisan view:cache
php artisan test
npm run build
```

`view:cache` encontra erros de sintaxe Blade. Os testes verificam os fluxos e o build confirma os recursos de frontend. Depois, faça uma verificação visual das páginas alteradas.

## Regra prática

Se a mudança define **o que deve acontecer**, provavelmente pertence ao controlador, serviço ou modelo. Se define **como deve aparecer**, pertence à view ou ao CSS. Se controla uma interação sem salvar dados, pode ficar no JavaScript da própria view.
