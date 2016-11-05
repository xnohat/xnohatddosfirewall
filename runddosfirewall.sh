#!/bin/sh 
tmux new-session -n 'xnohatDDoSFirewall' -d 'echo -e "Press Ctrl-b LEFT to move Cursor to this panel\nRun Command: php analyser.php"; bash -i' #create window with 1st panel
tmux split-window -h -p 50 'nload; bash -i' #split window horizon to 50%
tmux split-window -v -p 70 'top; bash -i' #split 'new created previous panel' to 2 panel with new create panel is 60% of previous panel
tmux split-window -v -p 40 'php logparser.php; bash -i' #split previous created panel to 2 panel with new create panel is 30% of previous panel
#tmux select-pane -t +1 #move focus to 1st panel
#tmux send-keys 'top; bash -i' Enter #run some command in 1st panel
tmux -2 attach-session -d
