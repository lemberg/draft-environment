# -*- mode: ruby -*-
# vi: set ft=ruby :

# This method recursively merges two nested hashes.
def merge_recursively(a, b)
  a.merge(b) {
    |key, a_item, b_item|

    if (a_item.class === "Hash" && b_item.class === "Hash")
      merge_recursively(a_item, b_item)
    else
      a_item.merge(b_item)
    end
  }
end
