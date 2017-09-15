# TVWS-Planning-Tool

## Guide
The graphical tool for TV white space deployment has the following options: 

**Select Base Station Information** - This is used to select the locations for determining the TVWS antenna placement. It has two options.  
* **Import Raw Data** - User can import base station information from .csv file according to a pre-defined format.
* **Auto Pick Input Locations** - By selecting this option, the base station information is imported automatically.

**Select Metric of Measurement** - It provides the user three metrics for TVWS antenna placement.
   * **Coverage** - Antenna locations are picked according to the geographical coverage area of the potential location.
   * **Population** - Base stations for deploying the TVWS antennas are selected in order of decreasing population.
   * **Weighted Utility** - Metric developed by us, which picks locations based on coverage-population tradeoff.

**Select Propagation Model** - Four propagation models are currently supported by the tool.
* **[Hata](https://en.wikipedia.org/wiki/Hata_Model)**
* **[Egli](https://en.wikipedia.org/wiki/Egli_model)**
* **[Free Space](https://en.wikipedia.org/wiki/Free-space_path_loss)**
* **[Plane Earth](http://www.wirelesscommunication.nl/reference/sampler/demo/pel.htm)**

**Theoretical Model Parameters** - These parameters are used in calculating the theoretical model throughput as per Shannon's theorem.
* **Receiver Sensitivity (dBm)** - Power level at the which the end-user can detect the RF signal.
* **Transmit Power (dBm)** - Power of the TVWS transmitter.
* **TVWS Frequency (MHz)** - Frequency at which the TVWS antenna operates.
* **Channel Width (MHz)** - Breadth of the TVWS signal for transmission.

When the user executes the theoretical model, the locations at which the TVWS antennas should be setup are displayed. On hovering over the antenna markers, one can observe all the information related to the antenna site such as latitude, longitude, altitude, coverage radius, users served, and the weighted average throughput of the antenna deployment.

## About
This tool is the outcome of ongoing EU-India REACH project in CSE department at IIT Bombay. It can be used by researchers and network engineers to plan the deployment of TV white space antennas. Presently, the tool models two varied geographies: Thane (India) and London (UK), characterized by rural and urban demography respectively. 

The research related to this project has been accepted as a full paper at CROWNCOM 2017. 

**Contacts:**
<br/>Mahesh Iyer
<br/>M. Tech. Student,
<br/>Department of Computer Science and Engineering,
<br/>IIT Bombay, Powai, Mumbai - 400076
<br/>Email - maheshm AT cse.iitb.ac.in

<br/>Prof. Mythili Vutukuru
<br/>Assistant Professor
<br/>Department of Computer Science and Engineering
<br/>IIT Bombay, Powai, Mumbai - 400076
<br/>Email - mythili AT cse.iitb.ac.in

Please contact us if you have any questions or requests.
