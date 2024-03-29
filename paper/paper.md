---
title: 'The Climate Equity Reference Calculator'
tags:
  - climate change
  - climate justice
  - effort sharing
  - NDCs
  - UNFCCC
  - mitigation
authors:
  - name: Ceecee Holz
    orcid: 0000-0003-0722-1044
    affiliation: 1
  - name: Eric Kemp-Benedict
    orcid: 0000-0001-5794-7172
    affiliation: 2
  - name: Tom Athanasiou
    affiliation: 3
  - name: Sivan Kartha
    orcid: 0000-0002-6014-2161
    affiliation: 2
affiliations:
 - name: Department of Geography and Environmental Studies, Carleton University, Ottawa
   index: 1
 - name: Stockholm Environment Institute US, Somerville
   index: 2
 - name: EcoEquity, Berkeley
   index: 3
date: 22 October 2018
bibliography: paper.bib
---

# Summary

In the context of the global response to the threat of anthropogenic climate change, the question arises of how to fairly share the overall global effort required to mitigate climate change. A rich body of literature exists that addresses this question, with several such effort-sharing approaches being proposed, reflecting different ethical positions with regards to fairness and justice. The Climate Equity Reference Project (CERP) framework [@Holz_Kartha_Athanasiou_2018], formerly called Greenhouse Development Rights (GDRs) [@Baer_et_al_2008; @Baer_2013] is such an approach. It takes nations' historical responsibility for greenhouse gas emissions and their financial capacity to reduce emissions and otherwise address climate change as the basis for assigning fair shares of the overall global effort. Importantly, it does so while at the same time protecting the right of the world's poorest to pursue a life free of poverty.

The Climate Equity Reference Calculator and its web interface, ``cerc-web``, allows users to calculate such fair share contributions for each country, based on a user’s selection of equity-related parameters. The ``cerc-web`` interface serves as a user interface to, and enhances the capabilities of the calculator "engine" backend. The "engine," written in C, has been released by the authors as a separate package [@EKB_Engine]. A technical documentation of the quantitative effort-sharing model that is implemented by the calculator is also available [@EKB_Calculations]. Taken together, the "engine" and ``cerc-web`` represent the only official implementation of the theoretical GDRs and CERP frameworks by their authors, although implementations by other teams exists [@Meinshausen_et_al_2015; @RobiouduPont_et_al_2017; @Pan_etal_2017], often obtaining substantially different results that have not yet been fully explored.

In addition to the calculator "engine," the calculator requires a "core database" as an external component, which contains historical data and future projections for greenhouse gas emissions, GDP, population, Gini coefficients and exchange rates for all countries. The default database, currently in version 7.2, is being frequently updated and is available online, together with its documentation [@Holz_etal_CoreDB_2018]. To our knowledge, at present only a single installation of ``cerc-web`` exists. It is located at https://calculator.climateequityreference.org and is maintained by the authors.

The web interface serves three distinct purposes: (i) to clarify the ethical choices before users and their implications, (ii) to visualize the results of the effort-sharing calculations, and (iii) to allow users to contrast the fair share results with actual climate action pledges made by countries.

On (i) the Climate Equity Reference framework is an effort sharing _framework_, meaning that it supports a virtually unlimited set of different possible choices with regards to the concrete parameterization of the framework and the calculator, each reflecting specific ethical positions. Particularly significant choices include start dates to calculate historical responsibility (from 1850 to "now"), different options to apply different treatment to the income of each country's poorest as opposed to its richest people in assessing a country’s capacity to act, the relative weight of responsibility and capability as well as the stringency of the global greenhouse gas emissions reductions pathway. The ``cerc-web`` user interface launches with a splash screen that clearly lays out the most salient of these choices along with commonly-chosen options, as well as help texts designed to assist users in understanding the ethical implications of their choices. Beyond the splash screen, the full interface allows for very granular tuning of each of the options available.

On (ii), the calculator "engine" takes as its input an SQLite [@SQLite] database that contains the "core database" as well as the specific choices selected by users through the ``cerc-web`` interface and likewise returns an SQLite database with the results of the effort-sharing calculations. While this allows the "engine" to run very fast and flexibly, it requires a front end to present the results in a more user-friendly manner for most users. The ``cerc-web`` interface serves this purpose by dynamically generating a number of different tables and reports, including a "country report" presenting the result data for any individual country, as well as several groups of countries, in graphical and tabular format. Owing to the ability of the calculator "engine" to perform whole model runs in seconds or fractions thereof, depending on hardware, the ``cerc-web`` user interface also allows dynamic updating of results tables and charts affording users to see in near-real-time the implications of changing their parameterization of the equity framework.

Finally, on (iii), ``cerc-web`` complements the core calculator "engine" functionality in that it, in its current version, allows end-users to compare countries' actually-articulated emissions reductions pledges with the results of the fair-share calculations based on users' specific ethical position, reflected through their inputs in the user interface. This is achieved through an auxiliary "pledge database" into which maintainers can enter emission reductions pledges as communicated by countries. The authors maintain a published list of their quantifications of these pledges [@Holz_NDCs] and also maintain the pledge database for the ``cerc-web`` instance at https://calculator.climateequityreference.org. ``cerc-web`` also includes a custom user interface for supporting users in managing the pledge database. Finally, for flexibility of use, ``cerc-web`` complements the capabilities of the calculator "engine" by exposing most of the calculator functionality to an API, which can be used by other projects to dynamically incorporate queries to the calculator. The API is comprehensibly documented elsewhere [@CERC_API].

``cerc-web`` and its results have been used extensively in research by the authors and others [most recently in @Holz_Kartha_Athanasiou_2018; @Norway_FairShares_2018; @CSO_Equity_Review_2018; @CSO_Equity_Review_2017; @CSO_Equity_Review_2016; @CSO_Equity_Review_2015; @ChristianAid_CHOGM_2018; @Metcalfe_2015; @Richards_Wollenberg_van_Vuuren_2018, among other pieces]. Several of these studies are cited in the recent Special Report on Global Warming of 1.5°C by the Intergovernmental Panel on Climate Change [@IPCC_SR15], highlighting the research relevance of the software. Especially the feature of ``cerc-web``, described under (iii) above, to allow comparison of countries' climate mitigation pledges with equitable shares of the global effort as calculated by ``cerc-web`` has enabled innovative research. With regards to the mitigation pledges submitted in the context of the Paris Agreement [@PA], this functionality enabled the first-ever research report that went beyond an aggregated, global-level assessment of these pledges [e.g., @UNEP_Gap_2015; @UNFCCC_NDC_Synth] and instead assessed all of these pledges individually relative to normatively-derived, quantitative benchmarks [@CSO_Equity_Review_2015; @Holz_Kartha_Athanasiou_2018].

# Acknowledgements

We acknowledge contributions of Tyler Kemp-Benedict in coding the user interface and the assistance of Douglas Wang in the early programming. Paul Baer was a founder and seminal contributor to the overall CERP/GDRs project for many years.

# References
