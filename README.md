# Filament Master Detail

Este package é um plugin Filament.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/:vendor_slug/:package_slug/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/:vendor_slug/:package_slug/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/:vendor_slug/:package_slug/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/:vendor_slug/:package_slug/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/:vendor_slug/:package_slug.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)

# Filament Master Details

Componente customizado para o [Filament Admin Panel](https://filamentphp.com), projetado para facilitar a adição e remoção de registros relacionados via relacionamento **HasMany (1,n)**, mesmo quando o registro pai ainda não foi persistido no banco de dados.

## Visão Geral

O **Filament Master Details** oferece uma solução prática e fluída para o gerenciamento de dados relacionados, especialmente útil em processos de criação onde a persistência imediata do registro pai ainda não ocorreu.

Este componente atua como um mini-formulário interno para os registros filhos, permitindo que sejam adicionados ou removidos em tempo real, armazenando os dados em memória até a persistência completa do formulário pai.

## Funcionalidades

- **Adicionar registros relacionados** sem exigir persistência prévia do registro principal.
- **Remover registros relacionados** dinamicamente.
- Integração com relacionamento Eloquent `hasMany`.
- Armazenamento em tempo real via Livewire para controle de estado.

> **Importante:** O componente **não possui paginação nem permite edição dos registros filhos**, sendo recomendado para relações com quantidade moderada de dados.

## Contexto de Uso

Ideal para formulários com:
- Cadastros rápidos com múltiplos sub-itens (ex: pedidos com produtos, turmas com alunos).
- Processos em que a experiência do usuário exige **agilidade e baixa fricção** no preenchimento de dados.
- Situações onde o modelo pai ainda não foi salvo.

## Requisitos

- Laravel >= 10.x
- Filament >= 3.x
- Livewire >= 3.x
