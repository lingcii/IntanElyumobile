# INTAN ELYU: A CROSS-PLATFORM SMART TOURISM MANAGEMENT SYSTEM FOR THE PROVINCIAL GOVERNMENT OF LA UNION

**A Project Documentation**  
Presented to the Faculty of Information Technology  
Saint Louis College  
City of San Fernando, La Union  

In Partial Fulfillment of the Requirements for the Course  
**IT 122 – Capstone Project 1**  

**by**  
VINCE SAIVANN R. ALVENDIA  
JAMES VERGEL B. GARCIA  
MARIA GABRIELLE T. LELINA  
GRACE ANNE A. MAGPANTAY  
CLAUDE BENEDICT N. NAVARRO  
STACY COELY P. SANCHEZ  

**July 2026**

---

# Chapter I
## INTRODUCTION

### Rationale
In the modern world, Information Technology (IT) is integral to enhancing the efficiency and effectiveness of organizational operations. Transforming manual processes into digital systems has significantly contributed to operational improvement in both public and private sectors (Nath et al., 2025). Manual processes—such as paper-based records, handwritten reports, and repetitive tasks—are often associated with inefficiencies, delays, and a higher risk of human error. The utilization of IT allows organizations to automate processes, securely manage data, and improve overall productivity, which is essential for addressing operational challenges and promoting sustainable growth (Muda et al., 2024).

Modern tourism heavily relies on smart technology to enhance trip planning, navigation, and visitor engagement. Global research shows that mobile tourism applications offering itinerary planning, real-time navigation, and location-based services significantly improve the tourist experience. At Chiang Mai University, Thailand, smart tourism studies highlight that travel apps now routinely include multi-stop itinerary planning, route optimization, digital ticketing, and even AI recommendations or augmented reality (AR) guides (Thinnukool et al., 2025). These technologies allow travelers to combine multiple destinations efficiently, reducing travel time and costs while providing up-to-date information. Consequently, tourists are better equipped to organize visits to both popular hotspots and remote sites by utilizing optimal routes and suggested points of interest.

In addition to navigation, automated cost estimation tools are emerging as vital features to help travelers budget their trips. Integrated budgeting features—which pull real-time fare data, entrance fees, and other expenses—can project the total trip cost before departure, thereby reducing financial uncertainty. A Philippine-based study (Estilo et al., 2023) found that 84% of surveyed tourists considered budget a “significant factor” in trip planning, and 76% prioritized staying within budget. By automating expense calculations, such tools allow visitors to allocate funds more effectively, greatly improving the efficiency and satisfaction of travel planning.

A major trend in recent tourism research is gamification and visitor engagement. Numerous studies find that integrating game elements—such as points, challenges, and rewards—into tourism apps significantly boosts visitor participation. For instance, a “play-to-earn” model, where users complete visits for points redeemable in-app, has become highly effective in destination marketing. Gamified systems deepen tourists’ engagement, make exploration more enjoyable, and encourage users to undertake curated activities that support local culture and businesses (Thinnukool et al., 2025). Location-based games like Pokémon GO and geocaching also demonstrate this effect; by offering rewards tied to visiting specific sites, they motivate travelers to seek out hidden or off-the-beaten-path attractions (Fernandez et al., 2025).

The tourism industry plays a critical role in the economic development of the Philippines, particularly in driving local commerce, creating livelihood opportunities, and promoting cultural preservation. According to the Department of Tourism (2025), the digital transformation of local travel sectors presents a massive opportunity for sustainable growth. However, local tourism sectors frequently face structural challenges, including the centralized congestion of travelers in limited coastal areas, difficulties in activity tracking, and a lack of real-time administrative insights for local authorities. These bottlenecks emphasize the critical need to enhance how provincial travel, navigation, and budgeting data are managed (Provincial Government of La Union, 2024; Trinidad, 2026).

A study titled *Smart Tourism Readiness and Visitor Satisfaction* in Metro Cebu underscores that traditional methods fall short of modern traveler expectations, frequently resulting in information gaps and uneven visitor distribution (Say, 2025). The research reveals that advanced digital infrastructure—specifically mobile applications, digital maps, and real-time tracking—holds the highest statistical influence on increasing visitor satisfaction. Adapting these findings through a web-based and mobile-based system with Smart Navigation, Automated Cost Estimation, and a Gamified Experience can provide critical real-time tourism analytics. This approach replaces delayed manual visitor logs with instant access to crowd-density and travel-pattern records, promoting equitable economic distribution and sustainable tourism management across the entire province.

For the Provincial Government of La Union (PGLU), the implementation of a Cross-Platform Smart Tourism Management System represents a strategic initiative to modernize provincial travel operations, enhance logistical efficiency, and strengthen service reliability. A centralized platform tracking tourist movement and dynamic travel expenses supports informed administrative and policy-driven decision-making. Therefore, this study is justified by the increasing complexity of regional travel ecosystems and the urgent need for integrated digital management solutions that promote economic equity, destination accountability, and sustainable tourism growth across all municipalities in La Union.

### Problem Definition
The La Union Provincial Tourism Office (LUPTO) currently faces challenges in modernizing its tourism sector to meet the expectations of digitally literate tourists. Although the province is a premier destination, the absence of a comprehensive digital infrastructure limits the PGLU’s ability to fully realize its tourism potential. This technological gap leads to operational inefficiencies and a fragmented experience for tourists.

The identified problems in the current process are as follows:

1. **Fragmented Trip Planning and Unpredictable Travel Costs**
   There is no centralized platform that supports organized trip planning. Tourists rely on manual budgeting, which often fails to accurately reflect changing transportation fares, entrance fees, and activity costs. Private vehicle users lack estimated fuel expenses, while public commuters face inconsistent fare rates. Consequently, tourists struggle to estimate total travel expenses, leading to an inconvenient travel experience.

2. **Unequal Distribution of Travelers and Limited Promotion of Secondary Spots**
   Tourists in La Union are primarily concentrated in popular destinations such as San Juan and San Fernando. According to LUPTO records (2025), 73% of tourist arrivals are recorded in these specific areas, while 12 out of 19 municipalities receive less than 5% of total visitors. Despite the availability of generic navigation tools, lesser-known "hidden gem" tourist spots remain untapped due to limited exposure and promotion.

3. **Low Visitor Engagement and Lack of Incentive to Explore**
   The current tourism setup lacks interactive elements to motivate tourists to visit lesser-known sites or return to the province. The absence of a gamified experience results in a passive journey, overlooking opportunities to utilize digital rewards, check-ins, or challenges that could boost the local economy in quieter areas and increase long-term visitor retention.

### Objective of the Study
The general objective of this study is to develop a Smart Tourism Management System for the La Union Provincial Tourism Office.

**Specifically, the study aims to:**
1. Implement an optimized itinerary planning module and real-time cost estimation tool to forecast trip expenses (including fuel costs, public transportation fares, and entrance fees), enabling tourists to make informed financial decisions.
2. Develop a smart navigation system that allows tourists to create efficient, multi-stop routes to both mainstream and lesser-known municipal destinations across La Union.
3. Integrate a gamified visitor engagement system utilizing digital check-ins and point-allocation e-rewards to encourage tourists to visit secondary destinations, allowing them to redeem accumulated Experience Points (XP) for local merchandise.

### Scope and Limitation
**Scope:**
*Intan Elyu: A Cross-Platform Smart Tourism Management System* functions as a comprehensive platform integrating travel coordination with administrative management, accessible via web browsers and a dedicated mobile application. This dual accessibility ensures management can oversee operations from desktops while travelers utilize the platform in real-time.

The system supports four distinct user roles: the PICTO, the LUPTO, the MTO, and the Tourists. **PICTO (Provincial Information and Communications Technology Office)** acts as the Super Administrator, ensuring technical integrity, system maintenance, and troubleshooting support. **LUPTO (La Union Provincial Tourism Office)** serves as the Administrator, moderating user feedback, overseeing cost calculation data, and monitoring provincial tourism analytics. **Municipal Tourism Office (MTO)** serves as the primary data administrator responsible for updating tourist site information, ongoing renovations, and localized road closures. Finally, **Tourists (Local and International)** utilize the system to plan itineraries, calculate estimated costs, and engage with gamified features.

The system offers the following features: (a) Tourist Spot Management; (b) Transportation Fare Management; (c) Interactive Tourism Map with routing functionality; (d) Smart Navigation, Itinerary Planner, and Automated Cost Estimation; (e) Gamification Engine and Leaderboards; (f) Tourist Spot Analytics; (g) Activity Logs; and (h) User Management.

**Limitations:**
The system requires a stable internet connection to retrieve real-time map data and may not reflect sudden, unannounced road closures. The automated cost estimation is based on standardized ranges stored within the system and does not account for personal incidental expenses, tips, or third-party price fluctuations. Theme-based itinerary recommendations rely on predefined templates and may not reflect real-time weather conditions. Real-time cost estimation depends on regular manual updates by PICTO and MTO administrators. The gamified experience relies on the GPS accuracy of the user’s mobile device, which may be affected in remote areas. Finally, while some destinations strictly require a tour guide, the system only provides informational notices and does not facilitate the direct booking of tour guide services.

### Conceptual Framework
**Republic Act No. 9593 (The Tourism Act of 2009)** recognizes tourism as a vital component of the national economy. In alignment with this law, the proposed system supports integrated promotional frameworks to improve provincial tourism management by digitizing itinerary coordination, optimizing routes, and automating travel forecasts.

**Republic Act No. 10173 (Data Privacy Act of 2012)** protects the fundamental human right of privacy. The system complies with this legislation by employing secure user role management, obtaining explicit consent for location-based check-ins, and safeguarding digital reward records.

**Republic Act No. 11032 (Ease of Doing Business and Efficient Government Service Delivery Act of 2018)** promotes the simplification of administrative procedures. The system aligns with this act by centralizing digital platforms, automating report functions, and improving service delivery speed across the province, directly contributing to SDG 11: Sustainable Cities and Communities.

### Definition of Terms
* **Tourism:** The activities of local and international visitors traveling to and staying in places outside their usual environment, specifically pertaining to the 19 municipalities of La Union.
* **Navigation:** The Smart Navigation module that utilizes map APIs to compute multi-stop route optimization and localized routing for tourists.
* **Cost Estimation:** The predictive process of forecasting financial expenses for an intended activity.
* **Automated Cost Estimation:** A module that aggregates real-time entry fees, transportation fares, and activity costs to provide travelers with an accurate pre-trip budget forecast.
* **Gamified:** The integration of game mechanics (points, achievements, digital rewards, location-based check-ins) into a non-game context to incentivize tourists.
* **Mobile-based:** An application software package designed for portable electronic devices (Android/iOS).
* **Web-based:** A software application stored on a remote server and delivered over the internet through a web browser.

### Hardware and Software Requirements

**Hardware Requirements**
| Hardware | Server (Hosting & Core APIs) | Admin (Web) | Client (Mobile) |
| :--- | :--- | :--- | :--- |
| **Memory** | Minimum: 8GB RAM / Recommended: 16GB RAM | Minimum: 4GB RAM / Recommended: 6GB RAM | Minimum: 4GB RAM / Recommended: 6GB RAM |
| **Processor** | Minimum: Quad-Core 2.0 GHz+ / Recommended: Octa-Core Xeon or EPYC | Minimum: Intel i3 or Ryzen 3 / Recommended: Intel i5 or Ryzen 5 | Minimum: Quad-core / Recommended: Octa-core Snapdragon or MediaTek |
| **Storage** | Minimum: 50GB SSD / Recommended: 100GB SSD | Minimum: 500MB Free Space / Recommended: 1GB Free Space | Minimum: 500MB Free Space / Recommended: 1GB Free Space |

**Software Requirements**
| Software | Server | Admin (Web) | Client (Mobile) |
| :--- | :--- | :--- | :--- |
| **Operating System** | Linux (Ubuntu Server 22.04 LTS / Debian) or Windows Server | Windows 10/11 | Android 10+, iOS 14+ |
| **Web Server** | Apache HTTP Server or Nginx | Local hosting environments (e.g., XAMPP, Laragon) | Standalone Application |
| **Backend Framework**| Laravel (PHP 8.2+) | Frontend Only (API Driven) | Frontend Only (API Driven) |
| **Database** | MySQL (version 8.0+) | Browser Local Storage | Local Storage |
| **Browser** | Not Required (Headless Server) | Google Chrome, Mozilla Firefox, Safari, Edge | In-App System WebView |
| **Libraries/Others** | Node.js, Composer | Vanilla JS, HTML5, CSS3, MapLibre GL JS | Vanilla JS, HTML5, CSS3, MapLibre GL JS |

---

# Chapter II
## DATA GATHERING METHOD

### Research Design
This study utilized a descriptive research design and a developmental research method. The descriptive research design seeks to observe, analyze, and accurately describe a population or phenomenon without manipulating variables (Ghanad, 2023). It involves the systematic collection of information to identify trends, allowing for a deeper understanding of the subject to inform effective decision-making during development (Sirisilla, 2023). 

The developmental research design was utilized to examine, investigate, and explore the processes, determinants, and trends linked to the advancement and transformation of the proposed tourism management system (Mahat et al., 2024).

### Locale of the Study
The primary locale of this study is the Provincial Government of La Union (PGLU), specifically the **La Union Provincial Tourism Office (LUPTO)** located at the Provincial Capitol, Aguila Rd., Brgy. II, City of San Fernando, La Union. LUPTO is the primary administrative body tasked with managing, monitoring, and promoting the tourism industry across the province's 19 municipalities and one component city.

Data validation regarding existing administrative workflows and technological infrastructures was conducted in coordination with the **Provincial Information and Communications Technology Office (PICTO)**. While field interviews focused on government operations in the capital city, the digital application framework is designed to encompass the entire geographical span of La Union.

### Primary Sources of Data
**Interviews**
A series of structured interviews were conducted to gather system requirements, identify existing operational challenges, and refine the proposed solution for LUPTO. Initial consultations with PICTO personnel were conducted to assess the current technological infrastructure and determine areas for improvement. Subsequent interviews with the Tourism Office provided deeper insights into their daily operations, services, and management of tourism-related activities.

Following the initial prototype development of the mobile application, additional interviews and validation sessions were held with the LUPTO assistant head. These sessions aimed to validate the gathered data and refine the system's proposed functionalities. Furthermore, consultations with the Land Transportation Franchising and Regulatory Board (LTFRB) were conducted to gather necessary technical information and accurate transportation fare data essential for the automated cost estimation module. 

### Secondary Sources of Data
**Internet Sources**
Existing academic literature and online scholarly publications related to smart tourism technologies, navigation systems, gamification, and automated cost estimation were extensively reviewed. These sources established the theoretical foundation and design framework of the system. Previous research demonstrates that intelligent navigation tools and gamified applications significantly enhance tourist engagement, operational efficiency, and the overall travel experience (Alghamdi & Alshammari, 2023; Fernandez, 2025; Pratama et al., 2023). 

**Unpublished Materials**
Archived capstone projects from the Don Mariano Marcos Memorial State University, College of Information Technology library were examined to establish a foundational understanding of system development methodologies and database architecture principles. Additionally, unpublished findings from the study *“ELYU-AR: A LOVE LA UNION TRAVEL GUIDE APP UTILIZING AUGMENTED REALITY”* by Gapuz et al. (2025) were referenced. These materials provided valuable insights into the practical implementation of IT solutions within local academic and governmental contexts.

### Ethical Consideration
The researchers strictly adhered to ethical and academic standards throughout the study. Prior to data collection and system development, formal letters of request and informed consent documents were provided to all interviewees and participants. These documents explicitly explained the purpose of the research, emphasized voluntary participation, assured confidentiality, and affirmed the participants’ right to withdraw at any time without consequence (Belmont & Patterson, 2024).

All data-gathering activities were conducted with transparency and respect for participants’ rights and privacy. Images, datasets, and external materials were utilized in strict compliance with copyright and data privacy laws, accompanied by proper citations. Tools such as Grammarly were utilized exclusively for grammar checking and plagiarism detection to ensure the originality and quality of the written content. The researchers remained fully accountable for maintaining the credibility of the study, protecting participants’ dignity, and upholding the institution’s standards in an ethical, transparent, and academically sound manner (Hutson, 2025; Wouters & Dijkhuizen, 2025).
