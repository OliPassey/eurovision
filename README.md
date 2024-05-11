# eurovision
 A vote collecting / displaying system for Eurovision Song Contest voting, at home with friends. Results page has auto-refresh option for displaying on a TV.

## Voting Page:
![image](https://user-images.githubusercontent.com/7745805/235271238-5175c4c3-a004-4edf-bcf0-3e2b57077634.png)

As you select countries for the vote, the song is displayed:
![image](https://user-images.githubusercontent.com/7745805/235271374-5e12b51e-2641-4c08-8c59-11fbe03be876.png)

## Results Page:
![image](https://user-images.githubusercontent.com/7745805/235271317-3150db75-655f-4856-840f-3ca03e94338e.png)

## Install / Docker:
System is designed to be run in Docker olipassey/eurovision  
You must have MongoDB running elsewhere in your network 
config.json should be held externally and path'd in to /var/www/html/config.json 

## Results
view_results.php should be running / displayed on a large screen whilst people are voting, and left open. There are a couple of easter egg leaderboards available.
