@commentEntity
Feature: Comment through JSON API

  Background:
    Given the current time is "2018-06-23T09:25:45+00:00"




  Scenario: Create a Comment with only required fields

    When I send a "POST" request to the API on "/api/comments" with body:
            """
           {
              "authorName": "momo2",
              "note": 0,
              "content": "momo2"
            }
            """
    Then I should see that the entity was created with response body:
            """
            {
                  "id": 1,
                  "authorName": "momo2",
                  "note": 0,
                  "content": "momo2"
              }
            """