<?php

function safer($input)
{
    return htmlspecialchars(trim($input));
}
