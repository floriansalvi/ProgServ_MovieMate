<?php

namespace ch;

/**
 * Interface I_Genre provides a contract for genres-related operations.
 */
interface I_Genre {

    /**
    * Retrieves information about all genres stored in the database and returns them.
    * 
    * @return array An array of genre retrieved from the database or an empty array if an error occurs.
    */
    public function getAllGenres():array;

    /**
    * Retrieves information about a specific genre stored in the database and return them.
    * 
    * @param int $id The id of the genre we want to retrieves information of
    *
    * @return array An array containing the genre's information or an empty array if an error occurs
    */
    public function getGenreDatas($id):array;
}