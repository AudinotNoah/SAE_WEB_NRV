<?php

namespace iutnc\nrv\action;



class DisplayStaffMenu extends Action
{
    public function execute(): string
    {

        return "
        <div class='content'>
            <h2>Menu du Staff</h2>
            <ul>
                <li><a href='index.php?action=add-soirees'>Ajouter une soirée</a></li>
                <li><a href='index.php?action=modif-soiree'>Modifier une soirée</a></li>
                <li><a href='index.php?action=add-spectacle'>Ajouter un spectacle</a></li>
                <li><a href='index.php?action=modif-spectacle'>Modifier un spectacle</a></li>
            </ul>
        </div>";
    }
}
