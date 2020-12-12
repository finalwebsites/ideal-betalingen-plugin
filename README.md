#iDEAL betalingen plugin

Met deze WordPress iDEAL plugin is het mogelijk om via je website een iDEAL betaling aan te bieden. Door het plaatsen van een shortcode kan je makkelijk een betaalpagina aanmaken, incl. order samenvatting, betaalformulier en een betaalbutton die de betaler gelijk naar de iDEAL pagina stuurt. De plugin is uitermate geschikt voor websites zonder webshop met een (klein) aantal producten en/of diensten.

##Functies
De iDEAL betalingen plugin beschikt over de volgende functies:
*Eenvoudig op elke pagina te plaatsen met behulp van een shortcode
*Voor elke betaalformulier kan een eigen e-mailbericht worden verstuurd. Hierdoor is de plugin geschikt voor de betaling van e-books, cursussen en (online) diensten.
*HTML en CSS is gebaseerd op het Bootstrap CSS framework. Dit framework is erg populair en werkt prima met premium WordPress templates die Visual composer ondersteunen.
*Voor het Divi WordPress thema is een extra CSS style optie beschikbaar.
*Mogelijkheid om extra adres- en bedrijfsgegevens via het betaalformulier te tonen.
*De betaler wordt gelijk doorgestuurd naar de iDEAL website van de gekozen bank en niet eerst naar de Mollie betaalpagina.
*Alle verstuurde formulieren worden opgeslagen binnen een custom post type (iDEAL betalingen). Hier kan je alle gegevens bekijken, inclusieve betaalstatus.
*Maak unieke bedankpagina’s aan voor verschillende formulieren.

##Installatie en configuratie
1.Upload het .zip bestand via de plugin sectie van het WordPress dashboard of met behulp van je sFTP programma.
1.Activeer de plugin en voer als eerste je Mollie API key (life of test) in.
1.Maak de bedankpagina aan en voor de post ID (deze vind je in de adresbalk wanneer je de pagina bewerkt) in op de instellingen pagina.
1.Wijzig de standaard teksten voor het formulier of de e-mail eenvoudig met de WordPress plugin “Loco Translate”.
1.Plaats de shortcode voor het betaalformulier in je pagina of post.

Bekijk ook de uitgebreide [handleiding voor het iDEAL formulier](https://www.finalwebsites.nl/handleiding-ideal-betalingen-plugin/).

##Updates

Versie 1.0.1 - 14 maart 2020
De volgende aanpassingen en updates hebben geen gevolgen voor de werking van de oude versie.

*Shortcode voor het formulier*
*Extra optie voor een (externe) bedankpagina URL
*Mogelijkheid om bedragen zonder BTW te berekenen (bijvoorbeeld bij donaties)

*Shortcode voor de bedankpagina*
Voor deze shortcode is er nu een optie (showordersummary) beschikbaar waardoor de bestelinformatie wel of niet getoond wordt.

*Bootstrap library wordt nu niet meer ingeladen*
De noodzakelijke CSS code voor het formulier (uit Bootstrap) staat nu in het style.css bestand.

*Nederlandse teksten*
 Alle teksten zijn veranderd naar het informele taalgebruik.

*Overige technische aanpassingen*
*De opties voor het SELECT menu met de banken worden nu in een aparte functie aangemaakt. Dit is nodig om de plugin te laten samenwerken met de WordPress plugin HTML Forms van ibericode.
*De functie voor het verwerken van de formuliergegevens had in de eerste versie te veel functionaliteiten. Deze zijn nu opgesplitst in meerdere functies. Hierdoor wordt het dan ook mogelijk om deze functies (later) in andere plugins te gebruiken (bijvoorbeeld in HTML Forms).


Versie 1.0.0
Eerste versie

## Licentie en disclaimer
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see [http://www.gnu.org/licenses/](http://www.gnu.org/licenses/).
