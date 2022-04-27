<?php
require_once ("Dbh.php");

/**
 * Class to handle articles
 */

class Article extends Dbh
{
    // Properties

    /**
     * @var int The article ID from the database
     */
    public $id = null;

    /**
     * @var int When the article was published
     */
    public $publicationDate = null;

    /**
     * @var string Full title of the article
     */
    public $title = null;

    /**
     * @var string A short summary of the article
     */
    public $summary = null;

    /**
     * @var string The HTML content of the article
     */
    public $content = null;


    /**
     * Sets the object's properties using the values in the supplied array
     *
     * @param assoc The property values
     */

    public function __construct($data = array())
    {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['publicationDate'])) $this->publicationDate = (int)$data['publicationDate'];
        if (isset($data['title'])) $this->title = preg_replace("/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['title']);
        if (isset($data['summary'])) $this->summary = preg_replace("/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['summary']);
        if (isset($data['content'])) $this->content = $data['content'];
    }


    /**
     * Sets the object's properties using the edit form post values in the supplied array
     *
     * @param assoc The form post values
     */

    public function storeFormValues($params)
    {

        // Store all the parameters
        $this->__construct($params);

        // Parse and store the publication date
        if (isset($params['publicationDate']))
        {
            $publicationDate = explode('-', $params['publicationDate']);

            if (count($publicationDate) == 3)
            {
                list ($y, $m, $d) = $publicationDate;
                $this->publicationDate = mktime(0, 0, 0, $m, $d, $y);
            }
        }
    }


    /**
     * Returns an Article object matching the given article ID
     *
     * @param int The article ID
     * @return Article|false The article object, or false if the record was not found or there was a problem
     */

    public function getById($id)
    {
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles WHERE id = ?";
        $st = $this->connect()->prepare($sql);
//        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute([$id]);
        $row = $st->fetch();
        $st = null;
        if ($row) return new Article($row);
    }


    /**
     * Returns all (or a range of) Article objects in the DB
     *
     * @param int Optional The number of rows to return (default=all)
     * @return Array|false A two-element array : results => array, a list of Article objects; totalRows => Total number of articles
     */

    public function getList($numRows = 1000000)
    {
        $dbh = $this->connect();

        //$sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles ORDER BY articles.publicationDate DESC LIMIT ?";
        $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles ORDER BY articles.publicationDate DESC LIMIT :numRows";

        $st = $dbh->prepare($sql);
        $st->bindValue(':numRows', $numRows, PDO::PARAM_INT);
        $st->execute();
        $list = array();

        $rows = $st->fetchAll();

        foreach ($rows as $row)
        {
            $article = new Article($row);
            $list[] = $article;
        }

        $totalRows = array();
        // Now get the total number of articles that matched the criteria
        $sql = "SELECT FOUND_ROWS() AS totalRows";
        $totalRows = $dbh->query($sql)->fetch();
        return (array("results" => $list, "totalRows" => $totalRows['totalRows'])); // -> parce que FETCH_ASSOC -> Sinon Both retourne [0] aussi...
    }


    /**
     * Inserts the current Article object into the database, and sets its ID property.
     */

    public function insert()
    {
        // Does the Article object already have an ID?
        if (!is_null($this->id)) trigger_error("Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR);

        // Insert the Article
        $sql = "INSERT INTO articles ( publicationDate, title, summary, content ) VALUES ( FROM_UNIXTIME(?), ?, ?, ?)";
        $st = $this->connect()->prepare($sql);
        $st->execute([$this->publicationDate, $this->title, $this->summary, $this->content]);
        $this->id = $this->connect()->lastInsertId();
    }


    /**
     * Updates the current Article object in the database.
     */

    public function update()
    {
        // Does the Article object have an ID?
        if (is_null($this->id)) trigger_error("Article::update(): Attempt to update an Article object that does not have its ID property set.", E_USER_ERROR);

        // Update the Article
        $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(?), title = ?, summary = ?, content = ? WHERE id = ?";
        $st = $this->connect()->prepare($sql);
        $st->execute([$this->publicationDate, $this->title, $this->summary, $this->content, $this->id]);
    }


    /**
     * Deletes the current Article object from the database.
     */

    public function delete()
    {
        // Does the Article object have an ID?
        if (is_null($this->id)) trigger_error("Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR);

        // Delete the Article
        $st = $this->connect()->prepare("DELETE FROM articles WHERE id = ? LIMIT 1");
        $st->execute([$this->id]);
    }
}
