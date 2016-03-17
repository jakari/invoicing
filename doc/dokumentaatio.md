# Invoicing-laskutusjärjestelmän dokumentaatio

Invoicing on www-pohjainen laskutusjärjestelmä, jolla on helppoa luoda uusia laskuja ja tulostaa niitä asiakkaille.

Järjestelmä on pienyrityksen käyttöä varten luotu ja siten sen ominaisuudet on varsin pienet, mutta riittävät, sillä laskutuksessa olisi tarkoitus käyttää mahdollisimman vähän aikaa.

## Järjestelmävaatimukset

### Palvelimen järjestelmävaatimukset

- PHP 5.6
- PostgreSQL 9.4

### Käyttäjän järjestelmävaatimukset

Käyttäjän selain kuuluu olla nykyaikainen ns. HTML5-tekniikoita tukeva selain.

## Käyttäjäryhmät

Käyttäjä
    Käyttäjä on henkilö jolla on täysi käyttöoikeus invoicing-laskutuspalvelussa.

## Käyttötapaukset

![Käyttötapaukaavio](kayttotapauskaavio.png)

### Laskun luonti

Laskun luonnissa käyttäjä kirjaa uuden laskun lisäämällä asiakastiedot, tuotteet ja niiden hinnat. Järjestelmä laskee laskun loppusumman, generoi laskulle laskunumeron ja viitenumeron.

### Laskun tulostus

Käyttäjä voi tulostaa luomansa laskun.
